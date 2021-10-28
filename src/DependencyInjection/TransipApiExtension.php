<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\DependencyInjection;

use Exception;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Jean85\PrettyVersions;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Transip\Api\Library\TransipAPI;
use Transip\Bundle\RestApi\HttpClient\Adapter\GenericHttpClient;
use Transip\Bundle\RestApi\HttpClient\Builder;

/**
 * @internal
 */
final class TransipApiExtension extends ConfigurableExtension
{
    private ?LoggerInterface $logger;

    public function __construct(
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger;
    }

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

        $this->setUpClientBuilder($container, $options['http_plugins'] ?? []);
        $this->setUpClient($container, $options);
    }

    private function setUpClient(ContainerBuilder $container, array $options): void
    {
        $token = $options['token'] ?? null;
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
            ->setArgument(5, new Reference(AdapterInterface::class))
            ->setArgument(6, $container
                ->setDefinition('transip.client.http.adapter', (new Definition(GenericHttpClient::class)))
                    ->setArgument(0, new Reference('transip.client.http'))
                    ->setArgument(1, $options['endpoint'] ?? TransipAPI::TRANSIP_API_ENDPOINT)
                    ->setPublic(false)
            );
    }

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
