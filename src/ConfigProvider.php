<?php

declare(strict_types=1);

namespace OLPS\PayumComponent;

use OLPS\PayumComponent\Factory\PayumFactory;
use OLPS\PayumComponent\Storage\Factory\PDOStorageFactory;
use OLPS\PayumComponent\Storage\PDOStorage;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Payment;
use Payum\Core\Model\Payout;
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
            'tokenFactoryUri' => '/',
            'gateways' => [],
            'storage' => [
                'default' => 'pdo',
                'factories' => [
                    'pdo' => [
                        'class' => PDOStorage::class,
                        'factory' => PDOStorageFactory::class,
                        'idkey' => 'id',
                    ],
                ],
                'models' => [
                    ArrayObject::class => [
                        'table' => 'payum_data',
                    ],
                    Payment::class => [
                        'factory' => 'pdo',
                        'table' => 'payum_payment',
                    ],
                    Payout::class => [
                        'factory' => 'default',
                    ],
                ],
            ],
            'token' => [
                'factory' => [
                    'baseUri' => '/',
                ],
                'storage' => [
                    'factory' => 'pdo',
                    'table' => 'payum_token',
                    'idkey' => 'hash',
                ],
            ],
        ];
    }
}
