<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\DependencyInjection;

use Becklyn\AssetsBundle\Asset\Asset;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class BecklynAssetsConfiguration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder ()
    {
        $treeBuilder = new TreeBuilder("becklyn_assets");

        $treeBuilder->getRootNode()
            ->children()
                ->append(self::appendNamespaces(
                    "All namespace directories, where assets are searched. Relative to `kernel.project_dir`."
                ))
                ->scalarNode("public_path")
                    ->defaultValue('%kernel.project_dir%/public')
                    ->info("The absolute path to the `public/` (or `web/`) directory.")
                ->end()
                ->scalarNode("output_dir")
                    ->defaultValue('assets')
                    ->info("The relative path to the assets output dir. Relative to `public_path`.")
                ->end()
                ->arrayNode("dependency_maps")
                    ->setDeprecated("The `becklyn_assets.dependency_maps` option is deprecated, as the the maps will always be automatically loaded.")
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                    ->info("The paths to the dependency maps. In asset notation: e.g. `@namespace/js/_dependencies.json`")
                ->end()
            ->end();

        return $treeBuilder;
    }


    /**
     * Appends the entries config entry.
     *
     * @return ArrayNodeDefinition|NodeDefinition
     */
    public static function appendNamespaces (string $description)
    {
        return (new TreeBuilder("namespaces"))->getRootNode()
            ->scalarPrototype()->end()
            ->validate()
            ->ifTrue(
                function (array $paths)
                {
                    foreach ($paths as $namespace => $path)
                    {
                        if (1 !== \preg_match('~^' . Asset::NAMESPACE_REGEX . '$~', $namespace))
                        {
                            return true;
                        }
                    }

                    return false;
                }
            )
            ->thenInvalid("The namespaces must start with a-z and can only contain a-z and 0-9.")
            ->end()
            ->validate()
            ->ifTrue(
                function (array $paths)
                {
                    foreach ($paths as $path)
                    {
                        if (!\is_string($path))
                        {
                            return true;
                        }

                        if (false !== \strpos($path, "..."))
                        {
                            return true;
                        }
                    }

                    return false;
                }
            )
            ->thenInvalid("The namespaces can't be outside of the project root (and can't use '..' in their paths).")
            ->end()
            ->info($description)
            ->defaultValue([]);
    }
}
