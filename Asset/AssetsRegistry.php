<?php

namespace Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\File\FileTypeRegistry;
use Becklyn\AssetsBundle\Storage\AssetStorage;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;


class AssetsRegistry implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $locator;


    /**
     *
     * @param ContainerInterface $locator
     */
    public function __construct (ContainerInterface $locator)
    {
        $this->locator = $locator;
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
        $cache = $this->locator->get(AssetsCache::class);
        $cachedAsset = $cache->get($asset);

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
        $fileTypeRegistry = $this->locator->get(FileTypeRegistry::class);
        $deferred = [];

        foreach ($assets as $asset)
        {
            if ($fileTypeRegistry->importDeferred($asset))
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
        $storage = $this->locator->get(AssetStorage::class);
        $cache = $this->locator->get(AssetsCache::class);

        $asset = $storage->import($asset);
        $cache->add($asset);

        return $asset;
    }



    /**
     * Clears the assets registry
     */
    public function clear () : void
    {
        $storage = $this->locator->get(AssetStorage::class);
        $cache = $this->locator->get(AssetsCache::class);

        $storage->removeAllStoredFiles();
        $cache->clear();
    }


    /**
     * @return array
     */
    public static function getSubscribedServices ()
    {
        return [
            AssetsCache::class,
            AssetStorage::class,
            FileTypeRegistry::class,
        ];
    }
}
