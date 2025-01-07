<?php

declare(strict_types=1);

namespace OLPS\PayumComponent\Factory;

use Payum\Payum;
use Psr\Container\ContainerInterface;

class PayumFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Payum($container);
    }
}   
