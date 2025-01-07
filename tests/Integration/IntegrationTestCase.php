<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PDO;

abstract class IntegrationTestCase extends TestCase
{
    protected static ?PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        $host = $_ENV['TEST_DB_HOST'];
        $dbname = $_ENV['TEST_DB_NAME'];
        $username = $_ENV['TEST_DB_USER'];
        $password = $_ENV['TEST_DB_PASS'];
        $port = $_ENV['TEST_DB_PORT'];

        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        self::$pdo = new PDO($dsn, $username, $password);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        parent::setUpBeforeClass();
    }
}
