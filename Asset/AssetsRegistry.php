<?php

namespace Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\File\FileTypeRegistry;


class AssetsRegistry
{
    /**
     * @var AssetsCache
     */
    private $cache;


    /**
     * @var AssetStorage
     */
    private $storage;


    /**
     * @var FileTypeRegistry
     */
    private $fileTypeRegistry;


    /**
     * @param AssetsCache      $cache
     * @param AssetStorage     $storage
     * @param FileTypeRegistry $fileTypeRegistry
     */
    public function __construct (AssetsCache $cache, AssetStorage $storage, FileTypeRegistry $fileTypeRegistry)
    {
        $this->cache = $cache;
        $this->storage = $storage;
        $this->fileTypeRegistry = $fileTypeRegistry;
    }


    /**
     * Gets the asset from the cache and adds it, if it is missing
     *
     * @param string $assetPath
     * @return Asset
     * @throws AssetsException
     */
    public function get (Asset $asset) : Asset
    {
        $cachedAsset = $this->cache->get($asset);

        if (null !== $cachedAsset)
        {
            return $cachedAsset;
        }

        return $this->addAsset($asset);
    }


    /**
     * Adds a list of asset paths
     *
     * @param Asset[] $assets
     * @throws AssetsException
     */
    public function add (array $assets, ?callable $progress) : void
    {
        $deferred = [];

        foreach ($assets as $asset)
        {
            if ($this->fileTypeRegistry->importDeferred($asset))
            {
                $deferred[] = $asset;
                continue;
            }

            $this->addAsset($asset);

            if (null !== $progress)
            {
                $progress();
            }
        }

        foreach ($deferred as $asset)
        {
            $this->addAsset($asset);

            if (null !== $progress)
            {
                $progress();
            }
        }
    }


    /**
     * Adds an asset to the registry
     *
     * @param string $assetPath
     * @return Asset
     * @throws AssetsException
     */
    private function addAsset (Asset $asset) : Asset
    {
        $asset = $this->storage->import($asset);
        $this->cache->add($asset);

        return $asset;
    }



    /**
     * Clears the assets registry
     */
    public function clear () : void
    {
        $this->storage->removeAllStoredFiles();
        $this->cache->clear();
    }
}
