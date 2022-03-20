<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Transip\Bundle\RestApi\DependencyInjection\TransipApiExtension;
use Transip\Bundle\RestApi\Tests\TestCase;
use Transip\Bundle\RestApi\TransipApiBundle;

final class ExtensionTest extends TestCase
{
    public function testLoadWithoutConfiguration(): void
    {
        $container = $this->createContainer();
        $container->registerExtension(new TransipApiExtension());
        $container->loadFromExtension('transip_api', []);
        $this->compileContainer($container);

        $definitions = $container->getDefinitions();
        self::assertArrayHasKey('transip.client.http.adapter', $definitions);
        self::assertArrayHasKey('transip.client.http', $definitions);
        self::assertArrayHasKey('transip.client', $definitions);
    }

    private function createContainer(): ContainerBuilder
    {
        return new ContainerBuilder(new ParameterBag([
            'kernel.cache_dir' => __DIR__,
            'kernel.build_dir' => __DIR__,
            'kernel.charset' => 'UTF-8',
            'kernel.debug' => true,
            'kernel.project_dir' => __DIR__,
            'kernel.bundles' => ['transip_api' => TransipApiBundle::class],
        ]));
    }

    private function compileContainer(ContainerBuilder $container): void
    {
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->getCompilerPassConfig()->setAfterRemovingPasses([]);
        $container->compile();
    }
}
