<?php

declare(strict_types=1);

namespace Tests\Unit;

use OLPS\PayumComponent\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testInvokeReturnsArray(): void
    {
        $provider = new ConfigProvider();
        
        $config = $provider();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('dependencies', $config);
    }
}
