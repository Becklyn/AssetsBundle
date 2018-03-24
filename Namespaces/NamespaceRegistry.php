<?php

namespace Becklyn\AssetsBundle\Namespaces;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Exception\AssetsException;


/**
 * Contains all namespaces for the application
 */
class NamespaceRegistry implements \IteratorAggregate
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
     * @param array  $namespaces
     */
    public function __construct (string $projectDir, array $namespaces = [])
    {
        $this->projectDir = $projectDir;

        foreach ($namespaces as $namespace => $directory)
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
        // prepend directory with project root, if not already there
        $dir = ($this->projectDir !== substr($directory, 0, strlen($this->projectDir)))
            ? "{$this->projectDir}/" . trim($directory, "/")
            : $directory;

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
     * Returns the full file path for the given asset
     *
     * @param Asset $asset
     * @return string
     * @throws AssetsException
     */
    public function getFilePath (Asset $asset) : string
    {
        return "{$this->getPath($asset->getNamespace())}/{$asset->getFilePath()}";
    }
}
