<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\DependencyInjection;

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
    public function load (array $configs, ContainerBuilder $container) : void
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

        // the namespace config, already prefixed by `kernel.project_dir`
        $prefixedNamespaces = $this->prefixPaths($config["namespaces"], $container->getParameter('kernel.project_dir'));

        // update services config with configuration values
        $container->getDefinition(AssetStorage::class)
            ->setArgument('$publicPath', $config["public_path"])
            ->setArgument('$outputDir', $config["output_dir"]);

        $container->getDefinition(NamespaceRegistry::class)
            ->setArgument('$namespaces', $prefixedNamespaces);

        $container->getDefinition(AssetsRouteLoader::class)
            ->setArgument('$outputDir', $config["output_dir"]);
    }


    /**
     * Prefixes the given paths automatically with the given prefix.
     *
     * @param array<string, string> $paths
     *
     * @return array<string, string>
     */
    private function prefixPaths (array $paths, string $prefix) : array
    {
        $result = [];

        foreach ($paths as $namespace => $path)
        {
            $result[$namespace] = "{$prefix}/" . \trim($path, "/");
        }

        return $result;
    }
}
