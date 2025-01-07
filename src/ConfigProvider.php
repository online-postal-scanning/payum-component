<?php

declare(strict_types=1);

namespace OLPS\PayumComponent;

use OLPS\PayumComponent\Storage\PDOStorage;
use OLPS\PayumComponent\Storage\Factory\PDOStorageFactory;

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
                PDOStorage::class  => PDOStorageFactory::class,
            ],
        ];
    }

    private function getPayumConfig(): array 
    {
        return [
            'storage' => [
                'token' => [
                    'table' => 'payum',
                    'idkey' => 'id',
                ],
            ],
        ];
    }
}
