<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\File\FilePath;

class Asset
{
    public const NAMESPACE_REGEX = '[a-z][a-z0-9_]*?';

    /**
     * The hashing algorithm to generate the fingerprints from. Must be compatible for both the `hash()` function
     * in PHP, as well as prefix in the HTML `integrity` attribute.
     */
    public const HASH_ALGORITHM = "sha256";

    //region Fields
    private string $namespace;
    private string $filePath;
    private ?string $hash = null;
    private ?string $fileNameHash = null;

    /**
     * @var array|string|string[]
     */
    private $fileType;
    //endregion


    public function __construct (string $namespace, string $filePath)
    {
        $this->namespace = $namespace;
        $this->filePath = \ltrim($filePath, "/");
        $this->fileType = \pathinfo($filePath, \PATHINFO_EXTENSION);
    }


    //region Accessors
    public function getNamespace () : string
    {
        return $this->namespace;
    }


    public function getFilePath () : string
    {
        return $this->filePath;
    }


    public function getHash () : ?string
    {
        return $this->hash;
    }


    public function setHash (?string $hash, bool $setFileNameHash = true) : void
    {
        if ($setFileNameHash && null !== $hash)
        {
            $fileNameHash = \rtrim($hash, "=");
            $fileNameHash = \strtr($fileNameHash, [
                "/" => "_",
            ]);
            $fileNameHash = \substr($fileNameHash, 0, 20);
        }
        else
        {
            $fileNameHash = null;
        }

        $this->hash = $hash;
        $this->fileNameHash = $fileNameHash;
    }


    /**
     * @return array|string|string[]
     */
    public function getFileType ()
    {
        return $this->fileType;
    }
    //endregion


    /**
     * Returns the full asset path.
     */
    public function getAssetPath () : string
    {
        return "@{$this->getNamespace()}/{$this->getFilePath()}";
    }


    /**
     * Returns the final storage path, where the production file is dumped to.
     */
    public function getDumpFilePath () : string
    {
        $dir = \dirname($this->filePath);
        $fileName = \basename($this->filePath, ".{$this->fileType}");

        $dir = "." === $dir
            ? ""
            : "{$dir}/";

        $hash = !empty($this->fileNameHash)
            ? ".{$this->fileNameHash}"
            : "";

        return "{$dir}{$fileName}{$hash}.{$this->fileType}";
    }


    /**
     * @throws AssetsException
     */
    public static function createFromAssetPath (string $assetPath) : self
    {
        if (1 === \preg_match('~^@(?<namespace>' . self::NAMESPACE_REGEX . ')/(?<path>.+)$~', $assetPath, $matches))
        {
            $path = \trim($matches["path"], "/");

            if ("" === $path)
            {
                throw new AssetsException("Invalid asset path – no path given.");
            }

            if (false !== \strpos($path, ".."))
            {
                throw new AssetsException("Invalid asset path – must not contain path '..'.");
            }

            return new self($matches["namespace"], $path);
        }

        throw new AssetsException(\sprintf(
            "Can't parse asset path: '%s'",
            $assetPath
        ));
    }


    /**
     * Returns an asset at a relative path (in relation to the current asset).
     *
     * @return self
     */
    public function getRelativeAsset (string $relativePath) : ?self
    {
        $filePath = new FilePath();

        return new self(
            $this->getNamespace(),
            $filePath->resolvePath($this->getFilePath(), $relativePath)
        );
    }
}
