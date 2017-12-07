<?php

namespace Becklyn\AssetsBundle\Asset;


use Becklyn\AssetsBundle\Exception\AssetsException;


class NamespacedAsset
{
    const NAMESPACE_REGEX = '[a-z][a-z0-9]*?';

    /**
     * @var string
     */
    private $namespace;


    /**
     * @var string
     */
    private $path;


    /**
     *
     * @param string $namespace
     * @param string $path
     */
    public function __construct (string $namespace, string $path)
    {
        $this->namespace = $namespace;
        $this->path = ltrim($path, "/");
    }


    /**
     * @return string
     */
    public function getNamespace () : string
    {
        return $this->namespace;
    }


    /**
     * @return string
     */
    public function getPath () : string
    {
        return $this->path;
    }


    /**
     * @param string $fullPath
     * @return NamespacedAsset
     * @throws AssetsException
     */
    public static function createFromFullPath (string $fullPath) : self
    {
        if (1 === \preg_match('~^@(?<namespace>' . self::NAMESPACE_REGEX . ')/(?<path>.+)$~i', $fullPath, $matches))
        {
            $path = trim($matches["path"], "/");

            if ("" === $path)
            {
                throw new AssetsException("Invalid asset path – no path given.");
            }

            if (false !== strpos($path, ".."))
            {
                throw new AssetsException("Invalid asset path – must not contain path '..'.");
            }

            return new self($matches["namespace"], $path);
        }

        throw new AssetsException(sprintf(
            "Can't parse asset path: '%s'",
            $fullPath
        ));
    }
}
