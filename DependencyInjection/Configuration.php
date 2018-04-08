<?php

namespace Jorijn\SymfonyBunqBundle\DependencyInjection;

use bunq\Util\BunqEnumApiEnvironmentType;
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
        $rootNode = $treeBuilder->root('jorijn_symfony_bunq');

        $rootNode->children()
            ->scalarNode('production_config_location')
            ->defaultValue('%kernel.project_dir%/bunq-production.conf')
            ->end()
            ->scalarNode('sandbox_config_location')
            ->defaultValue('%kernel.project_dir%/bunq-sandbox.conf')
            ->end()
            ->enumNode('environment')
            ->values([
                BunqEnumApiEnvironmentType::CHOICE_PRODUCTION,
                BunqEnumApiEnvironmentType::CHOICE_SANDBOX,
            ])
            ->defaultValue(BunqEnumApiEnvironmentType::CHOICE_PRODUCTION)
            ->end()
            ->scalarNode('application_description')
            ->defaultValue(\gethostname())
            ->end()
            ->arrayNode('allowed_ips')
            ->arrayPrototype()
            ->children()
            ->scalarNode('ip')
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
