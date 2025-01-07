<?php
declare(strict_types=1);

namespace OLPS\PayumComponent\Storage\Factory;

use OLPS\PayumComponent\Storage\PDOStorage;
use Payum\Core\Storage\StorageInterface;
use PDO;

final class PDOStorageFactory implements ModelStorageFactoryInterface
{
    public function __construct(
        private PDO $pdo,
    ){}

    public function create(string $model, array $config): PDOStorage|StorageInterface
    {
        return new PDOStorage(
            $this->pdo,
            $model,
            $config['table'],
            $config['idkey'],
        );
    }
}