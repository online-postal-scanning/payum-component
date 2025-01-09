<?php

declare(strict_types=1);

namespace OLPS\PayumComponent\Factory;

use Payum\Core\Bridge\PlainPhp\Security\TokenFactory;
use Payum\Core\Model\Token;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Storage\StorageInterface;
use Psr\Container\ContainerInterface;

class PayumFactory extends PayumBuilder
{
    private ContainerInterface $container;
    private string $default;
    private array $factories = [];

    public function __invoke(
        ContainerInterface $container,
    ): Payum
    {
        $this->container = $container;
        $config = $container->get('config')['payum'] ?? [];
        $this->default = $config['default'];

        $this->handleGateways($config['gateways']);
        $this->handleStorage($config['storage']); // must proceed handleToken
        $this->handleToken($config['token']);

        return $this->getPayum();
    }

    private function buildStorageFromConfig(string $model, array $config): StorageInterface
    {
        $factoryName = $config['factory'] ?? $this->default;
        if ($factoryName === strtolower('default')) {
            $factoryName = $this->default;
        }
        $factoryConfig = array_replace($this->factories[$factoryName]['config'], $config);

        $factory = $this->factories[$factoryName]['factory'];

        return $factory->create($model, $factoryConfig);
    }

    private function handleToken(array $config): void
    {
        $modelClass = $config['model'] ?? Token::class;
        $tokenStorage = $this->buildStorageFromConfig($modelClass, $config['storage']);
        $this->addStorage($modelClass, $tokenStorage);
        $this->setTokenStorage($tokenStorage); // needed for $this->tokenStorage

        $tokenFactory = new TokenFactory($this->tokenStorage, $this->buildRegistry($this->gateways, $this->storages), $config['factory']['baseUrl'] ?? null);
        $this->setTokenFactory($tokenFactory);
    }

    private function handleGateways($config): void
    {
        foreach ($config as $name => $gateway) {
            $this->addGateway($name, $gateway);
        }
    }

    private function handleStorage( array $config): void
    {
        $this->setUpFactories($config['factories']);

        foreach ($config['models'] as $model => $storage) {
            $this->addStorage($model, $this->buildStorageFromConfig($model, $storage));
        }
    }

    private function setUpFactories(array $storages): void
    {
        foreach ($storages as $name => $config) {
            $this->factories[$name]['config'] = $config;
            $this->factories[$name]['factory'] = $this->container->get($config['factory']);
        }
    }
}
