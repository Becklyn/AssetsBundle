<?php

namespace Becklyn\AssetsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;


class BecklynAssetsConfiguration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private $projectDir;


    /**
     * @param string $projectDir
     */
    public function __construct (string $projectDir)
    {
        $this->projectDir = $projectDir;
    }


    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder ()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('becklyn_assets');

        $rootNode
            ->children()
                ->arrayNode("entries")
                    ->scalarPrototype()->end()
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(
                            function (array $paths)
                            {
                                foreach ($paths as $namespace => $path)
                                {
                                    if (1 !== \preg_match('~^[a-z][a-z0-9]*$~', $namespace))
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

                                    $realPath = \realpath("{$this->projectDir}/" . ltrim($path, "/"));

                                    // skip not existing paths
                                    if (false === $realPath)
                                    {
                                        continue;
                                    }

                                    // invalid path given: is outside of project directory
                                    if ($this->projectDir !== substr($realPath, 0, strlen($this->projectDir)))
                                    {
                                        return true;
                                    }
                                }

                                return false;
                            }
                        )
                            ->thenInvalid("The entries can't be outside of the project root.")
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

                                    $realPath = \realpath("{$this->projectDir}/" . ltrim($path, "/"));

                                    // skip not existing paths
                                    if (false === $realPath)
                                    {
                                        continue;
                                    }

                                    // invalid path given: must be a directory
                                    if (!\is_dir($realPath))
                                    {
                                        return true;
                                    }
                                }

                                return false;
                            }
                        )
                            ->thenInvalid("The entries must be directories.")
                        ->end()
                    ->info("All entry directories, where assets are searched. Relative to `kernel.project_dir`.")
                ->end()
                ->scalarNode("public_path")
                    ->defaultValue('%kernel.project_dir%/public')
                    ->info("The absolute path to the `public/` (or `web/`) directory. Relative to `kernel.project_dir`.")
                ->end()
                ->scalarNode("output_dir")
                    ->defaultValue('assets')
                    ->info("The relative path to the assets output dir. Relative to `public_path`.")
                ->end()
            ->end();

        return $treeBuilder;
    }
}
