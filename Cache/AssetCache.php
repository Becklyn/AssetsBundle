<?php

namespace Becklyn\AssetsBundle\Cache;

use Becklyn\AssetsBundle\Data\AssetFile;
use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Data\CachedReference;
use Becklyn\AssetsBundle\Exception\InvalidCacheEntryException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;


class AssetCache
{
    /**
     * @var string
     */
    private $cacheStoragePath;


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
     * @var Filesystem
     */
    private $filesystem;



    /**
     * @param string               $rootDir
     * @param string               $cacheDir
     * @param string               $relativeAssetsDir the path where the cached assets should be stored (relative to /web/)
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
        $this->filesystem = new Filesystem();
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
        try
        {
            $assetFile = $this->generateAssetFile($reference);

            $key = $assetFile->getReference();

            // file is already cached, but under a different key
            if (in_array($key, $this->assetsCache) && $this->assetsCache[$key] !== $assetFile->getContentHash())
            {
                throw new InvalidCacheEntryException($key);
            }

            // copy file
            $this->copyFileToCache($assetFile);

            // store value in cache
            $this->assetsCache[$key] = [
                "contentHash" => $assetFile->getContentHash(),
                "hashFunction" => $assetFile->getHashAlgorithm(),
                "fileName" => $assetFile->getNewFileName(),
            ];
            $this->writeCacheFile();
        }
        catch (FileNotFoundException $e)
        {
            // file to cache does not exist
            if (null !== $this->logger)
            {
                $this->logger->warning("Can't add asset %asset% to cache: %message%", [
                    "%asset%" => $reference->getReference(),
                    "%message%" => $e->getMessage(),
                ]);
            }
        }
    }



    /**
     * Generates an asset file from a reference
     *
     * @param AssetReference $reference
     *
     * @return AssetFile
     */
    private function generateAssetFile (AssetReference $reference) : AssetFile
    {
        $filePath = $this->webDir . ltrim($reference->getReference(), "/");

        if (!is_file($filePath))
        {
            throw new FileNotFoundException(sprintf(
                "The assets file '%s' could not be found at path '%s'.",
                $reference->getReference(),
                $filePath
            ));
        }

        $newFilename = sha1_file($filePath);
        $contentHash = base64_encode(hash_file(AssetFile::INTEGRITY_HASH_FUNCTION, $filePath, true));
        return new AssetFile($reference, $filePath, $contentHash, $newFilename);
    }



    /**
     * Copies the given asset reference to the cache
     *
     * @param AssetFile $file
     */
    private function copyFileToCache (AssetFile $file)
    {
        $this->filesystem->copy(
            $file->getFilePath(),
            $this->assetsPath . $file->getNewFileName()
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
            $file = $this->assetsCache[$assetReference->getReference()];
            $hash = $file["contentHash"];
            $hashFunction = $file["hashFunction"];
            $fileName = $file["fileName"];

            return new CachedReference(
                "{$this->relativeAssetsDir}/{$fileName}",
                $hash,
                $hashFunction
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
        $this->filesystem->dumpFile($this->cacheStoragePath, $cacheContents);
    }



    /**
     * Clears the cache
     */
    public function clear ()
    {
        $this->filesystem->remove($this->assetsPath);
        $this->assetsCache = [];
        $this->writeCacheFile();
    }
}
