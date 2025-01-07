<?php
declare(strict_types=1);

namespace Tests\Integration\Storage;

use OLPS\PayumComponent\Storage\PDOStorage;
use Payum\Core\Model\Token;
use Payum\Core\Storage\StorageInterface;
use Tests\Integration\IntegrationTestCase;

class PDOStorageTest extends IntegrationTestCase
{
    private PDOStorage $tokenStorage;
    private Token $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create table if it doesn't exist
        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS payum_tokens (
                hash VARCHAR(255) PRIMARY KEY,
                `payum-data-model` TEXT
            )
        ");

        $this->tokenStorage = new PDOStorage(
            self::$pdo,
            Token::class,
            'payum_tokens',
            'hash'
        );

        $this->token = new Token();
        $this->token->setTargetUrl('https://example.com/payment');
        $this->token->setGatewayName('stripe');
        $this->token->setAfterUrl('https://example.com/success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Clean up table
        self::$pdo->exec("DROP TABLE IF EXISTS payum_tokens");
    }

    public function testImplementsStorageInterface(): void
    {
        $this->assertInstanceOf(StorageInterface::class, $this->tokenStorage);
    }

    public function testUpdateModel(): void
    {
        $this->tokenStorage->update($this->token);
        $this->assertNotNull($this->token->getHash());
        $foundToken = $this->tokenStorage->find($this->token->getHash());
        $this->assertEquals($this->token->getTargetUrl(), $foundToken->getTargetUrl());
        $this->assertEquals($this->token->getGatewayName(), $foundToken->getGatewayName());
        $this->assertEquals($this->token->getAfterUrl(), $foundToken->getAfterUrl());
    }

    public function testDeleteModel(): void
    {
        $this->tokenStorage->update($this->token);
        $hash = $this->token->getHash();
        $this->tokenStorage->delete($this->token);
        $this->assertNull($this->tokenStorage->find($hash));
    }

    public function testFindModelReturnsNullWhenNotFound(): void
    {
        $this->assertNull($this->tokenStorage->find('non-existent-hash'));
    }

    public function testIdentityMap(): void
    {
        $this->tokenStorage->update($this->token);
        $hash = $this->token->getHash();

        $firstFind = $this->tokenStorage->find($hash);
        $secondFind = $this->tokenStorage->find($hash);
        
        $this->assertSame($firstFind, $secondFind);
    }

    public function testUpdateExistingModel(): void
    {
        $this->tokenStorage->update($this->token);
        $hash = $this->token->getHash();
        
        $this->token->setTargetUrl('https://example.com/new-payment');
        $this->tokenStorage->update($this->token);
        
        $updatedToken = $this->tokenStorage->find($hash);
        $this->assertEquals('https://example.com/new-payment', $updatedToken->getTargetUrl());
    }
}
