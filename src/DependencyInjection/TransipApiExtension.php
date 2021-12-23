<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\DependencyInjection;

use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Transip\Api\Library\TransipAPI;
use Transip\Bundle\RestApi\HttpClient\Adapter\GenericHttpClient;
use Transip\Bundle\RestApi\HttpClient\Builder;

use function array_map;

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
        $locator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader  = new PhpFileLoader($container, $locator);
        $loader->load('services.php');

        $this->registerConfiguration($container, $mergedConfig);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function registerConfiguration(ContainerBuilder $container, array $config): void
    {
        $options = $config['options'] ?? [];

        if (isset($options['http_plugins'])) {
            $options['http_plugins'] = $this->configureHttpPlugins($options['http_plugins']);
        }

        $this->setUpClientBuilder($container, $options['http_plugins'] ?? []);
        $this->setUpClient($container, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function setUpClient(ContainerBuilder $container, array $options): void
    {
        $token          = $options['token'] ?? null;
        $authentication = $options['authentication'] ?? null;

        // TODO: Add warning logging here if both $token and $authentication are configured

        $client = $container
            ->setDefinition('transip.client', (new Definition(TransipAPI::class)))
            ->setPublic(false);

        if ($authentication) {
            $client->setArgument(0, $authentication['username'] ?? null)
                ->setArgument(1, $authentication['privateKey'] ?? null)
                ->setArgument(2, $options['generateWhitelistOnlyTokens'] ?? true)
                ->setArgument(3, '');
        } elseif ($token) {
            $client->setArgument(0, '')
                ->setArgument(1, '')
                ->setArgument(2, $options['generateWhitelistOnlyTokens'] ?? true)
                ->setArgument(3, $token);
        } else {
            $client->setArgument(0, '')
                ->setArgument(1, '')
                ->setArgument(2, $options['generateWhitelistOnlyTokens'] ?? true)
                ->setArgument(3, '');
        }

        $client->setArgument(4, $options['endpoint'] ?? TransipAPI::TRANSIP_API_ENDPOINT)
            ->setArgument(5, new Reference('cache.system'))
            ->setArgument(6, $container
                ->setDefinition('transip.client.http.adapter', (new Definition(GenericHttpClient::class)))
                ->setArgument(0, new Reference('transip.client.http'))
                ->setArgument(1, $options['endpoint'] ?? TransipAPI::TRANSIP_API_ENDPOINT)
                ->setPublic(false));
    }

    /**
     * @param Reference[] $plugins
     */
    private function setUpClientBuilder(ContainerBuilder $container, array $plugins): void
    {
        $clientBuilder = $container
            ->setDefinition('transip.client.http', (new Definition(Builder::class))
                ->setArgument(0, new Reference(HttpClient::class))
                ->setArgument(1, new Reference(RequestFactory::class)))
            ->setPublic(false);

        foreach ($plugins as $plugin) {
            $clientBuilder->addMethodCall('addPlugin', [$plugin]);
        }
    }

    /**
     * @param string[] $integrations
     *
     * @return array<Reference|Definition>
     */
    private function configureHttpPlugins(array $integrations): array
    {
        return array_map(static function (string $value): Reference {
            return new Reference($value);
        }, $integrations);
    }
}
