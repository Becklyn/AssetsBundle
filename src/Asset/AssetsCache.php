<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Asset;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * The main cache handler for assets.
 */
class AssetsCache
{
    public const CACHE_KEY = "becklyn.assets.cache";


    private CacheItemPoolInterface $cachePool;
    private CacheItemInterface $cacheItem;
    /** @var array<string, Asset> */
    private $assets;


    /**
     */
    public function __construct (CacheItemPoolInterface $pool)
    {
        $this->cachePool = $pool;
        $this->cacheItem = $pool->getItem(self::CACHE_KEY);
        $this->assets = $this->cacheItem->isHit() ? $this->cacheItem->get() : [];
    }


    /**
     * Returns the cached asset.
     */
    public function get (Asset $asset) : ?Asset
    {
        return $this->assets[$asset->getAssetPath()] ?? null;
    }


    /**
     * Adds an asset to the cache.
     */
    public function add (Asset $asset) : void
    {
        $this->assets[$asset->getAssetPath()] = $asset;
        $this->setAssets($this->assets);
    }


    /**
     * Clears the cache.
     */
    public function clear () : void
    {
        $this->setAssets([]);
    }


    /**
     * Sets and stores the new assets array.
     */
    private function setAssets (array $newAssets) : void
    {
        $this->assets = $newAssets;
        $this->cacheItem->set($this->assets);
        $this->cachePool->save($this->cacheItem);
    }
}
