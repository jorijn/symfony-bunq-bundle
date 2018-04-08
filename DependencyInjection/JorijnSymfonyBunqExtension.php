<?php

namespace Jorijn\SymfonyBunqBundle\DependencyInjection;

use bunq\Util\BunqEnumApiEnvironmentType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class JorijnSymfonyBunqExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('bunq.environment', $config['environment']);
        $container->setParameter(
            'bunq.configuration_file',
            BunqEnumApiEnvironmentType::CHOICE_PRODUCTION === $config['environment']
                ? $config['production_config_location']
                : $config['sandbox_config_location']
        );

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
