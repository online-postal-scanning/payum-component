<?php
declare(strict_types=1);

namespace OLPS\PayumComponent\Storage\Factory;

use Payum\Core\Storage\StorageInterface;

interface ModelStorageFactoryInterface
{
    /**
     * Creates a storage instance for the given model
     *
     * @param string $model The model class name
     * @param array $config Configuration for the storage
     * @return StorageInterface
     */
    public function create(string $model, array $config): StorageInterface;
}
