<?php

namespace Becklyn\AssetsBundle\Asset;


use Becklyn\AssetsBundle\Asset\Processor\AssetsProcessor;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Processor\AssetProcessorInterface;
use Becklyn\AssetsBundle\Processor\ProcessorRegistry;


class AssetsRegistry
{
    /**
     * @var AssetsCache
     */
    private $cache;


    /**
     * @var AssetGenerator
     */
    private $generator;


    /**
     * @var ProcessorRegistry
     */
    private $processorRegistry;


    /**
     *
     * @param AssetsCache       $cache
     * @param AssetGenerator    $generator
     * @param ProcessorRegistry $processorRegistry
     */
    public function __construct (AssetsCache $cache, AssetGenerator $generator, ProcessorRegistry $processorRegistry)
    {
        $this->cache = $cache;
        $this->generator = $generator;
        $this->processorRegistry = $processorRegistry;
    }


    /**
     * Gets the asset from the cache and adds it, if it is missing
     *
     * @param string $assetPath
     * @return Asset
     * @throws AssetsException
     */
    public function get (string $assetPath) : Asset
    {
        $asset = $this->cache->get($assetPath);

        return (null === $asset)
            ? $this->addAsset($assetPath)
            : $asset;
    }


    /**
     * Adds a list of asset paths
     *
     * @param string[] $assetPaths
     * @throws AssetsException
     */
    public function add (array $assetPaths, ?callable $progress) : void
    {
        $deferred = [];


        foreach ($assetPaths as $assetPath)
        {
            if ($this->processorRegistry->has($assetPath))
            {
                $deferred[] = $assetPath;
                continue;
            }

            $this->addAsset($assetPath);

            if (null !== $progress)
            {
                $progress();
            }
        }

        foreach ($deferred as $assetPath)
        {
            $this->addAsset($assetPath);

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
    private function addAsset (string $assetPath) : Asset
    {
        $asset = $this->generator->generateAsset($assetPath);
        $this->cache->add($assetPath, $asset);
        return $asset;
    }



    /**
     * Clears the assets registry
     */
    public function clear () : void
    {
        $this->generator->removeAllGeneratedFiles();
        $this->cache->clear();
    }
}
