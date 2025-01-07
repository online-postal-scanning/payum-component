<?php

declare(strict_types=1);

namespace OLPS\PayumComponent;

use OLPS\PayumComponent\Factory\PayumFactory;
use OLPS\PayumComponent\Storage\Factory\PDOStorageFactory;
use OLPS\PayumComponent\Storage\PDOStorage;
use Payum\Core\Model\Token;
use Payum\Core\Payum;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'payum' => $this->getPayumConfig(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'factories'  => [
                Payum::class  => PayumFactory::class,
            ],
        ];
    }

    private function getPayumConfig(): array 
    {
        return [
            'gateways' => [],
            'storage' => [
                'types' => [
                    'pdo' => [
                        'class' => PDOStorage::class,
                        'factory' => PDOStorageFactory::class,
                        'table' => 'payum',
                        'idkey' => 'id',
                    ],
                ],
                'token' => [
                    'type' => 'pdo',
                    'class' => Token::class,
                ],
            ],
        ];
    }
}
