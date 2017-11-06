<?php

namespace Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Path\AssetPathHelper;
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
     * @param CacheItemPoolInterface $pool
     * @param AssetGenerator         $generator
     * @param AssetPathHelper        $pathHelper
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __construct (CacheItemPoolInterface $pool, AssetGenerator $generator, AssetPathHelper $pathHelper)
    {
        $this->generator = $generator;
        $this->cacheItem = $pool->getItem(self::CACHE_KEY);
        $this->cachePool = $pool;
        $this->assets = $this->cacheItem->isHit() ? $this->cacheItem->get() : [];
        $this->assetPathHelper = $pathHelper;
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
     * Adds all assets to the cache
     *
     * @param array         $assetPaths
     * @param callable|null $progress
     * @throws AssetsException
     */
    public function addAll (array $assetPaths, ?callable $progress)
    {
        $cssFiles = [];

        foreach ($assetPaths as $assetPath)
        {
            // if this is a CSS file, save it for later processing
            if ($this->assetPathHelper->isCssFile($assetPath))
            {
                $cssFiles[] = $assetPath;
                continue;
            }

            // if this is any other asset except a CSS file, just process it directly
            $this->add($assetPath);

            if (null !== $progress)
            {
                $progress();
            }
        }

        foreach ($cssFiles as $cssFile)
        {
            if (null !== $progress)
            {
                $progress();
            }
        }
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
