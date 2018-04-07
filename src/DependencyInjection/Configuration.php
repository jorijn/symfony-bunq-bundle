<?php

namespace Jorijn\SymfonyBunqBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @see http://symfony.com/doc/current/cookbook/bundles/configuration.html
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('symfony_bunq');

        $rootNode->children()
            ->arrayNode('context_files')
                ->children()
                    ->scalarNode('sandbox')->end()
                    ->scalarNode('production')->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
