<?php

namespace Becklyn\AssetsBundle\Cache;

use Becklyn\AssetsBundle\Data\AssetFile;
use Becklyn\AssetsBundle\Data\CachedReference;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Handles the storing and loading of the reference to cached filename mapping
 */
class MappingCache
{
    /**
     * Mapping of asset reference (as specified in {% stylesheets %} or {% javascripts %}) to the cached entries:
     *
     * - contentHash
     * - hashAlgorithm
     * - fileName
     *
     * @var array.<string, array.<string, string>>
     */
    private $referenceMapping;


    /**
     * @var string
     */
    private $relativeAssetsDir;


    /**
     * @var Filesystem
     */
    private $filesystem;



    /**
     * @param string     $cacheDir
     * @param string     $relativeAssetsDir
     * @param Filesystem $filesystem
     */
    public function __construct (string $cacheDir, string $relativeAssetsDir, Filesystem $filesystem)
    {
        $this->cacheFile = $cacheDir . "/becklyn/assets/assets_mapping.php";
        $this->relativeAssetsDir = trim($relativeAssetsDir, "/");
        $this->filesystem = $filesystem;
        $this->referenceMapping = $this->loadFromCache();
    }



    /**
     * Loads the mapping from the cache
     *
     * @return array
     */
    private function loadFromCache () : array
    {
        $assetsCache = null;

        if (is_file($this->cacheFile))
        {
            $assetsCache = @include $this->cacheFile;
        }

        return is_array($assetsCache)
            ? $assetsCache
            : [];
    }



    /**
     * Returns the cached reference
     *
     * @param string $reference
     *
     * @return CachedReference|null
     */
    public function get (string $reference)
    {
        $cachedData = $this->referenceMapping[$reference] ?? null;

        if (null === $cachedData)
        {
            return null;
        }

        return new CachedReference(
            "{$this->relativeAssetsDir}{$cachedData['fileName']}",
            $cachedData["contentHash"],
            $cachedData["hashAlgorithm"]
        );
    }



    /**
     * @param AssetFile $file
     */
    public function add (AssetFile $file)
    {
        $this->referenceMapping[$file->getReference()] = [
            "contentHash" => $file->getContentHash(),
            "hashAlgorithm" => $file->getHashAlgorithm(),
            "fileName" => $file->getNewFileName(),
        ];

        $this->writeCacheFile();
    }



    /**
     * Clears the cache
     */
    public function clear ()
    {
        $this->referenceMapping = [];
        $this->writeCacheFile();
    }


    /**
     * Writes the content to the file system (Assets Cache Table)
     */
    private function writeCacheFile ()
    {
        $cacheContents = '<?php return ' . var_export($this->referenceMapping, true) . ';';
        $this->filesystem->dumpFile($this->cacheFile, $cacheContents);
    }
}
