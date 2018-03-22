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
     * @var CacheItemPoolInterface
     */
    private $cachePool;


    /**
     * @var \Psr\Cache\CacheItemInterface
     */
    private $cacheItem;


    /**
     * @var array<string, Asset>
     */
    private $assets = [];


    /**
     * @param CacheItemPoolInterface $pool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __construct (CacheItemPoolInterface $pool)
    {
        $this->cachePool = $pool;
        $this->cacheItem = $pool->getItem(self::CACHE_KEY);
        $this->assets = $this->cacheItem->isHit() ? $this->cacheItem->get() : [];
    }


    /**
     * Returns the cached asset
     *
     * @param Asset $asset
     *
     * @return Asset|null
     */
    public function get (Asset $asset) : ?Asset
    {
        return $this->assets[$asset->getAssetPath()] ?? null;
    }


    /**
     * Adds an asset to the cache
     *
     * @param string $assetPath
     *
     * @throws AssetsException
     */
    public function add (Asset $asset) : void
    {
        $this->assets[$asset->getAssetPath()] = $asset;
        $this->setAssets($this->assets);
    }


    /**
     * Clears the cache
     */
    public function clear () : void
    {
        $this->setAssets([]);
    }


    /**
     * Sets and stores the new assets array
     *
     * @param array $newAssets
     */
    private function setAssets (array $newAssets) : void
    {
        $this->assets = $newAssets;
        $this->cacheItem->set($this->assets);
        $this->cachePool->save($this->cacheItem);
    }
}
