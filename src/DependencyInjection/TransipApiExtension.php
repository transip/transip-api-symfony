<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\DependencyInjection;

use Exception;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Jean85\PrettyVersions;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Transip\Bundle\RestApi\HttpClient\Builder;

/**
 * @internal
 */
final class TransipApiExtension extends ConfigurableExtension
{
    /**
     * @param mixed[] $mergedConfig
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $this->registerConfiguration($container, $mergedConfig);
    }

    private function registerConfiguration(ContainerBuilder $container, array $config): void
    {
        $options = $config['options'] ?? [];

        if (isset($options['http_plugins'])) {
            $options['http_plugins'] = $this->configureHttpPlugins($options['http_plugins'], $config);
        }

        $container
            ->register('transip.client.options', Options::class)
            ->setPublic(false)
            ->setArgument(0, $options);

        $serializer = (new Definition(Serializer::class))
            ->setPublic(false)
            ->setArgument(0, new Reference('transip.client.options'));

        $representationSerializerDefinition = (new Definition(RepresentationSerializer::class))
            ->setPublic(false)
            ->setArgument(0, new Reference('transip.client.options'));

        $factory = $container
            ->setDefinition('transip.client.http', (new Definition(Builder::class))
                ->setArgument(0, new Reference(HttpClient::class))
                ->setArgument(1, new Reference(RequestFactory::class)))
            ->setPublic(false);

        foreach (($options['http_plugins'] ?? []) as $plugin) {
            $factory->addMethodCall('addPlugin', [$plugin]);
        }
    }

    /**
     * @param string[]             $integrations
     * @param array<string, mixed> $config
     *
     * @return array<Reference|Definition>
     */
    private function configureHttpPlugins(array $integrations, array $config): array
    {
        return array_map(static function (string $value): Reference {
            return new Reference($value);
        }, $integrations);
    }
}
