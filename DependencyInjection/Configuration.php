<?php

namespace Becklyn\AssetsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('becklyn_assets')
                    ->children()
                        ->arrayNode('output')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('base_path')
                                    ->defaultValue('%kernel.root_dir%/../web')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('css_directory')
                                    ->defaultValue('assets/css')
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('js_directory')
                                    ->defaultValue('assets/js')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('caching')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('path')
                                    ->defaultValue('%kernel.root_dir%/var/becklyn_assets')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                    ->end();

        return $treeBuilder;
    }
}
