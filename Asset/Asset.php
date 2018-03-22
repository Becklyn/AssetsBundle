<?php

namespace Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Exception\AssetsException;


class Asset
{
    const NAMESPACE_REGEX = '[a-z][a-z0-9_]*?';

    //region Fields
    /**
     * @var string
     */
    private $namespace;


    /**
     * @var string
     */
    private $filePath;


    /**
     * @var string|null
     */
    private $hash;
    //endregion


    /**
     * @param string $filePath
     * @param string $hash
     */
    public function __construct (string $namespace, string $filePath)
    {
        $this->namespace = $namespace;
        $this->filePath = $filePath;
    }


    //region Accessors
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
    public function getFilePath () : string
    {
        return $this->filePath;
    }


    /**
     * @return null|string
     */
    public function getHash () : ?string
    {
        return $this->hash;
    }


    /**
     * @param null|string $hash
     */
    public function setHash (?string $hash) : void
    {
        $this->hash = $hash;
    }
    //endregion


    /**
     * @param string $assetPath
     * @return Asset
     * @throws AssetsException
     */
    public static function createFromAssetPath (string $assetPath) : Asset
    {
        if (1 === \preg_match('~^@(?<namespace>' . self::NAMESPACE_REGEX . ')/(?<path>.+)$~', $assetPath, $matches))
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
            $assetPath
        ));
    }
}
