<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Namespaces;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Exception\AssetsException;

/**
 * Contains all namespaces for the application.
 */
class NamespaceRegistry implements \IteratorAggregate
{
    public const THROW_ON_MISSING = true;
    public const IGNORE_MISSING = false;

    /** @var array<string,string> */
    private $namespaces = [];


    /**
     * @param array<string, string> $namespaces
     */
    public function __construct (array $namespaces = [])
    {
        foreach ($namespaces as $namespace => $directory)
        {
            $this->addNamespace($namespace, $directory);
        }
    }


    /**
     * Adds a namespace.
     *
     * @param string $directory the absolute path to the assets directory
     *
     * @throws AssetsException
     */
    public function addNamespace (string $namespace, string $directory, bool $failOnMissingDir = self::THROW_ON_MISSING) : void
    {
        if (isset($this->namespaces[$namespace]))
        {
            throw new AssetsException(\sprintf(
                "Duplicate registration of namespace '%s'.",
                $namespace
            ));
        }

        if (!\is_dir($directory))
        {
            if (self::THROW_ON_MISSING === $failOnMissingDir)
            {
                throw new AssetsException(\sprintf(
                    "Can't find assets dir when registering namespace '%s': %s",
                    $namespace,
                    $directory
                ));
            }

            return;
        }

        $this->namespaces[$namespace] = \rtrim($directory, "/");
    }


    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator ()
    {
        return new \ArrayIterator($this->namespaces);
    }


    /**
     * Returns the path to the namespace.
     *
     * @throws AssetsException
     */
    public function getPath (string $namespace) : string
    {
        if (!isset($this->namespaces[$namespace]))
        {
            throw new AssetsException(\sprintf(
                "Unknown entry namespace: '%s'",
                $namespace
            ));
        }

        return $this->namespaces[$namespace];
    }


    /**
     * Returns the full file path for the given asset.
     *
     * @throws AssetsException
     */
    public function getFilePath (Asset $asset) : string
    {
        return "{$this->getPath($asset->getNamespace())}/{$asset->getFilePath()}";
    }
}
