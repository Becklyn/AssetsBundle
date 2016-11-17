<?php

namespace Becklyn\AssetsBundle\Cache;

use Becklyn\AssetsBundle\Cache\MappingCache\MappingCacheIO;
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
     * @var MappingCacheIO
     */
    private $cacheIO;



    /**
     * @param string         $relativeAssetsDir
     * @param MappingCacheIO $cacheIO
     */
    public function __construct (string $relativeAssetsDir, MappingCacheIO $cacheIO)
    {
        $this->relativeAssetsDir = trim($relativeAssetsDir, "/");
        $this->cacheIO = $cacheIO;
        $this->referenceMapping = $this->cacheIO->load();
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
        $this->cacheIO->write($this->referenceMapping);
    }
}
