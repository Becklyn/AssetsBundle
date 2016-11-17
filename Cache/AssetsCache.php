<?php

namespace Becklyn\AssetsBundle\Cache;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Data\CachedReference;
use Becklyn\AssetsBundle\Exception\InvalidCacheEntryException;
use Becklyn\AssetsBundle\File\AssetFileGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;


class AssetsCache
{
    /**
     * @var LoggerInterface|null
     */
    private $logger;


    /**
     * @var FileCache
     */
    private $fileCache;


    /**
     * @var MappingCache
     */
    private $mappingCache;


    /**
     * @var AssetFileGenerator
     */
    private $assetFileGenerator;



    /**
     * @param AssetFileGenerator   $assetFileGenerator
     * @param FileCache            $fileCache
     * @param MappingCache         $mappingCache
     * @param LoggerInterface|null $logger
     */
    public function __construct (AssetFileGenerator $assetFileGenerator, FileCache $fileCache, MappingCache $mappingCache, LoggerInterface $logger = null)
    {
        $this->assetFileGenerator = $assetFileGenerator;
        $this->fileCache = $fileCache;
        $this->mappingCache = $mappingCache;
        $this->logger = $logger;
    }




    /**
     * Adds the key-value pair to the temporary cache and upon calling build() to the file system
     *
     * @param AssetReference $reference
     */
    public function add (AssetReference $reference)
    {
        try
        {
            $assetFile = $this->assetFileGenerator->generateAssetFile($reference);
            $existingCacheReference = $this->mappingCache->get($reference->getReference());

            // file is already cached, but under a different key
            if (null !== $existingCacheReference && $existingCacheReference->getContentHash() !== $assetFile->getContentHash())
            {
                throw new InvalidCacheEntryException($reference);
            }

            // add file to cache
            $this->fileCache->add($assetFile);
            $this->mappingCache->add($assetFile);
        }
        catch (FileNotFoundException $e)
        {
            // file to cache does not exist
            if (null !== $this->logger)
            {
                $this->logger->warning("Can't add asset %asset% to cache: %message%", [
                    "%asset%" => $reference->getReference(),
                    "%message%" => $e->getMessage(),
                ]);
            }
        }
    }



    /**
     * Retrieves the cached value by key
     *
     * @param AssetReference $assetReference
     *
     * @return CachedReference|null
     */
    public function get (AssetReference $assetReference)
    {
        $cachedReference = $this->mappingCache->get($assetReference);

        if (null !== $cachedReference)
        {
            return $cachedReference;
        }

        if (null !== $this->logger)
        {
            $this->logger->warning("No asset found for '%reference%'.", [
                "%reference%" => $assetReference->getReference(),
            ]);
        }

        return null;
    }



    /**
     * Clears the cache
     */
    public function clear ()
    {
        $this->fileCache->clear();
        $this->mappingCache->clear();
    }
}
