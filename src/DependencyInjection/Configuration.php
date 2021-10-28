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
