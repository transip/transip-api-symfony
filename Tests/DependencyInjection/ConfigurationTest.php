<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Transip\Bundle\RestApi\DependencyInjection\Configuration;
use Transip\Bundle\RestApi\Tests\TestCase;

final class ConfigurationTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), []);

        $this->assertEquals(self::getBundleDefaultConfig(), $config);
    }

    private static function getBundleDefaultConfig(): array
    {
        return [];
    }
}
