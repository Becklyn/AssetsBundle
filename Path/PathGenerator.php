<?php

namespace Becklyn\AssetsBundle\Path;

use Becklyn\AssetsBundle\Cache\AssetsCache;
use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Data\DisplayableAssetInterface;


class PathGenerator
{
    /**
     * @var AssetsCache
     */
    private $cache;


    /**
     * @var bool
     */
    private $debug;



    /**
     * @param AssetsCache $cache
     * @param bool        $debug
     */
    public function __construct (AssetsCache $cache, bool $debug)
    {
        $this->cache = $cache;
        $this->debug = $debug;
    }



    /**
     * Returns the relative URL for the asset reference
     *
     * @param AssetReference $reference
     *
     * @return DisplayableAssetInterface|null
     */
    public function getDisplayAssetReference (AssetReference $reference)
    {
        // if debug mode, return the reference unchanged
        if ($this->debug)
        {
            return $reference;
        }

        return $this->cache->get($reference);
    }
}
