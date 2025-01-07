<?php

declare(strict_types=1);

namespace OLPS\PayumComponent\Factory;

use Payum\Core\PayumBuilder;
use Payum\Payum;
use Psr\Container\ContainerInterface;

class PayumFactory
{
    public function __invoke(ContainerInterface $container): Payum
    {
        $builder = new PayumBuilder();


        foreach ($config['gateways'] as $name => $gateway) {
            $builder->addGateway($name, $gateway);
        }

        $builder->setTokenStorage($this->tokenStorageFactory($config['tokenStorage']));

        // $builder->addStorage($modelClass, $storage);
        return $builder->getPayum();
    }

    private function tokenStorageFactory(array $config): StorageInterface
    {
        
    }

}   
