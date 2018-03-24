<?php

namespace Becklyn\AssetsBundle\DependencyInjection;

use Becklyn\AssetsBundle\Dependency\DependencyLoader;
use Becklyn\AssetsBundle\Dependency\DependencyMap;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Becklyn\AssetsBundle\RouteLoader\AssetsRouteLoader;
use Becklyn\AssetsBundle\Storage\AssetStorage;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


class BecklynAssetsExtension extends Extension
{
    /**
     * @inheritdoc
     */
    public function load (array $configs, ContainerBuilder $container)
    {
        // process config
        $config = $this->processConfiguration(
            new BecklynAssetsConfiguration(),
            $configs
        );

        // load services
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . "/../Resources/config")
        );
        $loader->load("services.yaml");

        // update services config with configuration values
        $container->getDefinition(AssetStorage::class)
            ->setArgument('$publicPath', $config["public_path"])
            ->setArgument('$outputDir', $config["output_dir"]);

        $container->getDefinition(NamespaceRegistry::class)
            ->setArgument('$namespaces', $config["namespaces"]);

        $container->getDefinition(AssetsRouteLoader::class)
            ->setArgument('$outputDir', $config["output_dir"]);

        $this->initializeDependencyMap($config, $container);
    }


    /**
     * Initializes the dependency map
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function initializeDependencyMap (array $config, ContainerBuilder $container) : void
    {
        $registry = new NamespaceRegistry($container->getParameter("kernel.project_dir"), $config["namespaces"]);
        $loader = new DependencyLoader($registry);

        foreach ($config["dependency_maps"] as $dependencyMap)
        {
            $loader->importFile($dependencyMap);
        }

        $container->getDefinition(DependencyMap::class)
            ->setArgument('$dependencyMap', $loader->getDependencyMap());
    }
}
