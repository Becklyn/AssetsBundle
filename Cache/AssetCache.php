<?php

namespace Becklyn\AssetsBundle\Cache;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Data\CachedReference;
use Becklyn\AssetsBundle\Exception\InvalidCacheEntryException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;


class AssetCache
{
    /**
     * @var string
     */
    private $cacheStoragePath = "assets_cache_table.php";


    /**
     * @var array.<string, string>
     */
    private $assetsCache = null;


    /**
     * @var string
     */
    private $webDir;


    /**
     * @var string
     */
    private $assetsPath;


    /**
     * @var string
     */
    private $relativeAssetsDir;


    /**
     * @var LoggerInterface|null
     */
    private $logger;



    /**
     * @param string               $rootDir
     * @param string               $cacheDir
     * @param string               $relativeAssetsDir
     * @param LoggerInterface|null $logger
     *
     * @internal param KernelInterface $kernel
     */
    public function __construct (string $rootDir, string $cacheDir, string $relativeAssetsDir, LoggerInterface $logger = null)
    {
        $this->webDir = dirname($rootDir) . "/web/";
        $this->relativeAssetsDir = rtrim($relativeAssetsDir, "/");
        $this->assetsPath = $this->webDir . ltrim($relativeAssetsDir, "/");
        $this->cacheStoragePath = "{$cacheDir}/becklyn/assets/assets_mapping.php";
        $this->assetsCache = $this->loadCache();
        $this->logger = $logger;
    }



    /**
     * Loads the current cache data
     *
     * @return array
     */
    private function loadCache () : array
    {
        $assetsCache = null;

        if (is_file($this->cacheStoragePath))
        {
            $assetsCache = @include $this->cacheStoragePath;
        }

        return is_array($assetsCache)
            ? $assetsCache
            : [];
    }



    /**
     * Adds the key-value pair to the temporary cache and upon calling build() to the file system
     *
     * @param AssetReference $reference
     */
    public function add (AssetReference $reference)
    {
        $filePath = $this->getFilePath($reference);

        // file to cache does not exist
        if (!is_file($filePath))
        {
            if (null !== $this->logger)
            {
                $this->logger->warning("Can't add asset %asset% to cache, as the file was not found at %path%.", [
                    "%asset%" => $reference->getReference(),
                    "%path%" => $filePath,
                ]);
            }

            return;
        }

        $hash = $this->hashFileContent($filePath);
        $key = $reference->getReference();

        // file is already cached, but under a different key
        if (in_array($key, $this->assetsCache) && $this->assetsCache[$key] !== $hash)
        {
            throw new InvalidCacheEntryException($key);
        }

        // copy file
        $this->copyFileToCache($reference, $hash);

        // store value in cache
        $this->assetsCache[$key] = $hash;
        $this->writeCacheFile();
    }



    /**
     * @param AssetReference $reference
     *
     * @return string
     */
    private function getFilePath (AssetReference $reference)
    {
        return $this->webDir . ltrim($reference->getReference(), "/");
    }



    /**
     * Returns the hash of the file content
     *
     * @param string $filePath
     *
     * @return string
     */
    private function hashFileContent (string $filePath) : string
    {
        return sha1_file($filePath);
    }



    /**
     * Copies the given asset reference to the cache
     *
     * @param AssetReference $reference
     * @param string         $hash
     */
    private function copyFileToCache (AssetReference $reference, string $hash)
    {
        $filesystem = new Filesystem();
        $filesystem->copy(
            $this->getFilePath($reference),
            $this->assetsPath . $hash . "." . $reference->getTypeFileExtension()
        );
    }



    /**
     * Retrieves the cached value by key
     *
     * @param AssetReference $assetReference
     *
     * @return CachedReference|null
     */
    public function get (AssetReference $assetReference)
    {
        if (isset($this->assetsCache[$assetReference->getReference()]))
        {
            $hash = $this->assetsCache[$assetReference->getReference()];
            return new CachedReference(
                "{$this->relativeAssetsDir}/{$hash}.{$assetReference->getTypeFileExtension()}",
                $hash
            );
        }

        if (null !== $this->logger)
        {
            $this->logger->warning("No asset found for '%reference%'.", [
                "%reference%" => $assetReference->getReference(),
            ]);
        }

        return null;
    }


    /**
     * Writes the content to the file system (Assets Cache Table)
     */
    private function writeCacheFile ()
    {
        $cacheContents = '<?php return ' . var_export($this->assetsCache, true) . ';';
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($this->cacheStoragePath, $cacheContents);
    }



    /**
     * Clears the cache
     */
    public function clear ()
    {
        $this->assetsCache = [];
        $this->writeCacheFile();
    }
}
