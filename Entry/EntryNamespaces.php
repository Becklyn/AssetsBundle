<?php

namespace Becklyn\AssetsBundle\Entry;

use Becklyn\AssetsBundle\Asset\NamespacedAsset;
use Becklyn\AssetsBundle\Exception\AssetsException;


/**
 * Contains all namespaces for the application
 */
class EntryNamespaces implements \IteratorAggregate
{
    /**
     * @var array<string,string>
     */
    private $namespaces = [];


    /**
     * @var string
     */
    private $projectDir;

    /**
     * @param string $projectDir
     * @param array  $entries
     */
    public function __construct (string $projectDir, array $entries)
    {
        $this->projectDir = $projectDir;

        foreach ($entries as $namespace => $directory)
        {
            $this->addNamespace($namespace, $directory);
        }
    }


    /**
     * Adds a namespace
     *
     * @param string $namespace
     * @param string $directory
     * @throws AssetsException
     */
    public function addNamespace (string $namespace, string $directory) : void
    {
        $dir = "{$this->projectDir}/" . trim($directory, "/");

        if (isset($this->namespaces[$namespace]))
        {
            throw new AssetsException(sprintf(
                "Duplicate registration of namespace '%s'.",
                $namespace
            ));
        }

        if (is_dir($dir))
        {
            $this->namespaces[$namespace] = $dir;
        }
        else
        {
            throw new AssetsException(sprintf(
                "Can't find assets dir when registering namespace '%s': %s",
                $namespace,
                $dir
            ));
        }
    }


    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator ()
    {
        return new \ArrayIterator($this->namespaces);
    }


    /**
     * Returns the path to the namespace
     *
     * @param string $namespace
     * @return string
     * @throws AssetsException
     */
    public function getPath (string $namespace) : string
    {
        if (!isset($this->namespaces[$namespace]))
        {
            throw new AssetsException(sprintf(
                "Unknown entry namespace: '%s'",
                $namespace
            ));
        }

        return $this->namespaces[$namespace];
    }


    /**
     * Returns the full file path for the given namespaced asset
     *
     * @param NamespacedAsset $asset
     * @return string
     * @throws AssetsException
     */
    public function getFilePath (NamespacedAsset $asset) : string
    {
        return "{$this->getPath($asset->getNamespace())}/{$asset->getPath()}";
    }
}
