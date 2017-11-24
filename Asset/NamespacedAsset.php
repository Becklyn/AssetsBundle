<?php

namespace Becklyn\AssetsBundle\Asset;


use Becklyn\AssetsBundle\Exception\AssetsException;


class NamespacedAsset
{
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
        if (1 === \preg_match('~^@(?<namespace>[^/]*?)/(?<path>.*)$~', $fullPath, $matches))
        {
            return new self($matches["namespace"], $matches["path"]);
        }

        throw new AssetsException(sprintf(
            "Can't parse asset path: '%s'",
            $fullPath
        ));
    }
}
