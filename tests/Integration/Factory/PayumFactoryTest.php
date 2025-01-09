<?php
declare(strict_types=1);

namespace Tests\Integration\Factory;

use OLPS\PayumComponent\Factory\PayumFactory;
use OLPS\PayumComponent\Storage\Factory\PDOStorageFactory;
use OLPS\PayumComponent\Storage\PDOStorage;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Payment;
use Payum\Core\Model\Payout;
use Payum\Core\Model\Token;
use Payum\Core\Payum;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Tests\Integration\IntegrationTestCase;

class PayumFactoryTest extends IntegrationTestCase
{
    private PayumFactory $factory;
    private ContainerInterface|MockObject $container;
    private array $config;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary tables
        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS payum_token (
                hash VARCHAR(255) PRIMARY KEY,
                `payum-data-model` TEXT
            )
        ");
        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS payum_payment (
                id VARCHAR(255) PRIMARY KEY,
                `payum-data-model` TEXT
            )
        ");
        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS payum_payout (
                id VARCHAR(255) PRIMARY KEY,
                `payum-data-model` TEXT
            )
        ");
        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS payum_data (
                id VARCHAR(255) PRIMARY KEY,
                `payum-data-model` TEXT
            )
        ");

        $this->config = [
            'payum' => [
                'default' => 'pdo',
                'gateways' => [
                    'offline' => [
                        'factory' => 'offline',
                    ],
                ],
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
                            'table' => 'payum_payout',
                        ],
                    ],
                ],
                    'token' => [
                        'factory' => [
                            'baseUrl' => '/',
                        ],
                        'storage' => [
                            'factory' => 'pdo',
                            'table' => 'payum_token',
                            'idkey' => 'hash',
                        ],
                    ],
            ]
        ];

        // Set up container mock
        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->method('get')
            ->willReturnCallback(function ($id) {
                if ($id === 'config') {
                    return $this->config;
                }
                if ($id === PDOStorageFactory::class) {
                    return new PDOStorageFactory(self::$pdo);
                }
                return null;
            });

        $this->factory = new PayumFactory();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up tables
        self::$pdo->exec("DROP TABLE IF EXISTS payum_token");
        self::$pdo->exec("DROP TABLE IF EXISTS payum_payment");
        self::$pdo->exec("DROP TABLE IF EXISTS payum_data");
    }

    public function testInvokeReturnsPayum(): void
    {
        $payum = ($this->factory)($this->container);
        
        $this->assertInstanceOf(Payum::class, $payum);
    }

    public function testTokenStorageIsConfigured(): void
    {
        $payum = ($this->factory)($this->container);
        
        $storage = $payum->getStorage(Token::class);
        $this->assertInstanceOf(StorageInterface::class, $storage);
        
        // Test storage functionality
        $token = new Token();
        $token->setTargetUrl('https://example.com/payment');
        $storage->update($token);
        
        $foundToken = $storage->find($token->getHash());
        $this->assertEquals('https://example.com/payment', $foundToken->getTargetUrl());
    }

    public function testModelStoragesAreConfigured(): void
    {
        $payum = ($this->factory)($this->container);
        
        // Test ArrayObject storage
        $arrayObjectStorage = $payum->getStorage(ArrayObject::class);
        $this->assertInstanceOf(StorageInterface::class, $arrayObjectStorage);
        
        $model = new ArrayObject(['test' => 'value']);
        $arrayObjectStorage->update($model);
        
        $identity = $arrayObjectStorage->identify($model);
        $foundModel = $arrayObjectStorage->find($identity->getId());
        $this->assertEquals('value', $foundModel['test']);

        // Test Payment storage
        $paymentStorage = $payum->getStorage(Payment::class);
        $this->assertInstanceOf(StorageInterface::class, $paymentStorage);
        
        $payment = new Payment();
        $payment->setNumber('TEST123');
        $paymentStorage->update($payment);
        
        $identity = $paymentStorage->identify($payment);
        $foundPayment = $paymentStorage->find($identity->getId());
        $this->assertEquals('TEST123', $foundPayment->getNumber());
    }

    public function testGatewaysAreConfigured(): void
    {
        $payum = ($this->factory)($this->container);
        
        $gateway = $payum->getGateway('offline');
        $this->assertNotNull($gateway);
    }
}
