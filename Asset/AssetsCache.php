<?php

namespace Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Exception\AssetsException;
use Psr\Cache\CacheItemPoolInterface;


/**
 * The main cache handler for assets
 */
class AssetsCache
{
    const CACHE_KEY = "becklyn.assets.cache";


    /**
     * @var AssetGenerator
     */
    private $generator;


    /**
     * @var array<string, Asset>
     */
    private $assets = [];


    /**
     * @param string $rootDir
     */
    public function __construct (CacheItemPoolInterface $pool, AssetGenerator $generator)
    {
        $this->generator = $generator;
        $this->cacheItem = $pool->getItem(self::CACHE_KEY);
        $this->cachePool = $pool;
        $this->assets = $this->cacheItem->isHit() ? $this->cacheItem->get() : [];
    }


    /**
     * Returns the cached asset or adds it, if it doesn't exist yet.
     *
     * @param string $assetPath
     *
     * @throws AssetsException
     */
    public function get (string $assetPath) : Asset
    {
        return $this->assets[$assetPath] ?? $this->add($assetPath);
    }


    /**
     * Adds an asset to the cache
     *
     * @param string $assetPath
     * @return Asset the generated asset
     *
     * @throws AssetsException
     */
    public function add (string $assetPath) : Asset
    {
        $asset = $this->generator->generateAsset($assetPath);

        $this->assets[$assetPath] = $asset;
        $this->cacheItem->set($this->assets);
        $this->cachePool->save($this->cacheItem);

        return $asset;
    }


    /**
     * Clears the cache
     */
    public function clear ()
    {
        $this->generator->removeAllGeneratedFiles();
        $this->assets = [];
    }
}
