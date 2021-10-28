<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('transip');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('endpoint')
                    ->info('If this value is not provided, the API client will use the default endpoint.')
                ->end()
                ->scalarNode('token')
                    ->info('Use this to authenticate the API client with a pre-defined access token.')
                ->end()
                ->arrayNode('authentication')
                    ->children()
                        ->scalarNode('username')
                            ->info('Your TransIP username.')
                        ->end() // Username
                        ->scalarNode('privateKey')
                            ->info('The private key generated from the Control Panel.')
                        ->end() // privateKey
                    ->end()
                ->end() // authentication
                ->arrayNode('options')
                    ->children()
                        ->arrayNode('http_plugins')
                            /* Add cast for simple array list */
                            ->scalarPrototype()->end()
                        ->end() // http_plugins
                    ->end()
                ->end() // options
            ->end()
        ;

        return $treeBuilder;
    }
}
