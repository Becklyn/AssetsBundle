<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Dependency;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface|null
     */
    private $logger;


    /**
     * @param NamespaceRegistry    $namespaceRegistry
     * @param LoggerInterface|null $logger
     */
    public function __construct (NamespaceRegistry $namespaceRegistry, ?LoggerInterface $logger = null)
    {
        $this->namespaceRegistry = $namespaceRegistry;
        $this->logger = $logger;

        // automatically try to load `@namespace/js/_dependencies.json` for every namespace
        foreach ($namespaceRegistry as $namespace => $path)
        {
            $this->importFile("@{$namespace}/js/_dependencies.json");
        }
    }


    /**
     * Imports all dependencies from the given dependencies file.
     *
     * @param string $assetPathToMap
     */
    public function importFile (string $assetPathToMap) : void
    {
        try {
            $filePath = $this->namespaceRegistry->getFilePath(Asset::createFromAssetPath($assetPathToMap));

            if (\is_file($filePath))
            {
                $map = \json_decode(\file_get_contents($filePath), true);

                if (null !== $map)
                {
                    $this->importMap(
                        \dirname($assetPathToMap),
                        $map
                    );
                }
            }
        }
        catch (AssetsException $e)
        {
            if (null !== $this->logger)
            {
                $this->logger->error("Could not load dependency map at {path}.", [
                    "path" => $assetPathToMap,
                    "error" => $e->getMessage(),
                ]);
            }
        }
    }


    /**
     * Imports dependencies from the given map.
     *
     * @param string $basePath
     * @param array  $dependencyMap
     */
    public function importMap (string $basePath, array $dependencyMap) : void
    {
        $basePath = \rtrim($basePath, "/");

        foreach ($dependencyMap as $file => $dependencies)
        {
            foreach ($dependencies as $dependency)
            {
                if ("js" === \pathinfo($dependency, \PATHINFO_EXTENSION))
                {
                    $this->dependencyMap["{$basePath}/{$file}.js"][] = "{$basePath}/{$dependency}";
                }
            }
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
