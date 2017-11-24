<?php

namespace Becklyn\AssetsBundle\Entry;


use Becklyn\AssetsBundle\Asset\NamespacedAsset;
use Becklyn\AssetsBundle\Exception\AssetsException;


class EntryNamespaces implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $namespaces;


    /**
     * @param string $projectDir
     * @param array  $entries
     */
    public function __construct (string $projectDir, array $entries)
    {
        $this->namespaces = [];

        foreach ($entries as $namespace => $entry)
        {
            $dir = "{$projectDir}/" . trim($entry, "/");

            if (is_dir($dir))
            {
                $this->namespaces[$namespace] = $dir;
            }
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
    public function getFilePath (NamespacedAsset $asset)
    {
        return "{$this->getPath($asset->getNamespace())}/{$asset->getPath()}";
    }
}
