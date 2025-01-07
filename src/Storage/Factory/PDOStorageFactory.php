<?php
declare(strict_types=1);

namespace OLPS\PayumComponent\Storage\Factory;

use OLPS\PayumComponent\Storage\PDOStorage;
use PDO;
use Psr\Container\ContainerInterface;

final class PDOStorageFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): PDOStorage {


        return new PDOStorage(
            $container->get(PDO::class),
            $config['storageToken']['table'],
            $config['storageToken']['idkey'],
        );
    }
}