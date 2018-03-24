<?php

namespace Becklyn\AssetsBundle\Dependency;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;


class DependencyLoader
{
    /**
     * @var array
     */
    private $dependencyMap = [];


    /**
     * @var NamespaceRegistry
     */
    private $namespaceRegistry;


    /**
     * @param NamespaceRegistry $namespaceRegistry
     */
    public function __construct (NamespaceRegistry $namespaceRegistry)
    {
        $this->namespaceRegistry = $namespaceRegistry;
    }


    /**
     * Imports all dependencies from the given dependencies file
     *
     * @param string $assetPathToMap
     * @throws \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function importFile (string $assetPathToMap) : void
    {
        $filePath = $this->namespaceRegistry->getFilePath(Asset::createFromAssetPath($assetPathToMap));
        $map = \json_decode(\file_get_contents($filePath), true);
        $this->importMap(
            \dirname($assetPathToMap),
            $map
        );
    }


    /**
     * Imports dependencies from the given map
     *
     * @param string $basePath
     * @param array  $dependencyMap
     */
    public function importMap (string $basePath, array $dependencyMap) : void
    {
        $basePath = rtrim($basePath, "/");

        foreach ($dependencyMap as $file => $dependencies)
        {
            $this->dependencyMap["{$basePath}/{$file}"] = \array_map(
                function (string $file) use ($basePath)
                {
                    return "{$basePath}/{$file}";
                },
                $dependencies
            );
        }
    }


    /**
     * @return array
     */
    public function getDependencyMap () : array
    {
        return $this->dependencyMap;
    }
}
