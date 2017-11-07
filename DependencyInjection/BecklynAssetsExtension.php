<?php

namespace Becklyn\AssetsBundle\DependencyInjection;


use Becklyn\AssetsBundle\Asset\AssetGenerator;
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
        $config = $this->processConfiguration(new BecklynAssetsConfiguration(), $configs);

        // load services
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . "/../Resources/config")
        );
        $loader->load("services.yaml");

        // update services config with configuration values
        $assetGenerator = $container->getDefinition(AssetGenerator::class);
        $assetGenerator
            ->setArgument('$publicPath', $config["public_path"])
            ->setArgument('$outputDir', $config["output_dir"]);
    }
}
