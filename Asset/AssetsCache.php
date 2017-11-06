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
     * @var array<string, Asset>
     */
    private $assets = [];


    /**
     * @param CacheItemPoolInterface $pool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __construct (CacheItemPoolInterface $pool)
    {
        $this->cacheItem = $pool->getItem(self::CACHE_KEY);
        $this->cachePool = $pool;
        $this->assets = $this->cacheItem->isHit() ? $this->cacheItem->get() : [];
    }


    /**
     * Returns the cached asset
     *
     * @param string $assetPath
     *
     * @return Asset|null
     */
    public function get (string $assetPath) : ?Asset
    {
        return $this->assets[$assetPath] ?? null;
    }


    /**
     * Adds an asset to the cache
     *
     * @param string $assetPath
     *
     * @throws AssetsException
     */
    public function add (string $assetPath, Asset $asset)
    {
        $this->assets[$assetPath] = $asset;
        $this->cacheItem->set($this->assets);
        $this->cachePool->save($this->cacheItem);
    }


    /**
     * Clears the cache
     */
    public function clear ()
    {
        $this->assets = [];
        $this->cacheItem->set($this->assets);
        $this->cachePool->save($this->cacheItem);
    }
}
