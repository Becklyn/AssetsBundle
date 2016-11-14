<?php

namespace Becklyn\AssetsBundle\Path;

use Becklyn\AssetsBundle\Cache\AssetCache;
use Becklyn\AssetsBundle\Data\AssetReference;
use Symfony\Component\Asset\Packages;


class PathGenerator
{
    /**
     * @var AssetCache
     */
    private $cache;


    /**
     * @var Packages
     */
    private $packages;


    /**
     * @var bool
     */
    private $debug;



    /**
     * @param AssetCache $cache
     * @param Packages   $packages
     * @param bool       $debug
     */
    public function __construct (AssetCache $cache, Packages $packages, bool $debug)
    {
        $this->cache = $cache;
        $this->packages = $packages;
        $this->debug = $debug;
    }



    /**
     * Returns the relative URL for the asset reference
     *
     * @param AssetReference $reference
     *
     * @return string
     */
    public function getRelativeUrl (AssetReference $reference)
    {
        return $reference->getReference();
    }
}
