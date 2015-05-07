<?php


namespace Becklyn\AssetsBundle\Service;


use Becklyn\AssetsBundle\Cache\AssetCacheBuilder;
use Becklyn\AssetsBundle\Entity\AssetCollection;
use Becklyn\AssetsBundle\Entity\StatusMessage;
use Becklyn\AssetsBundle\Exception\AssetFileNotFoundException;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class AssetsDumper extends ContainerAware
{
    /**
     * @var AssetConfigurationService
     */
    private $configurationService;


    /**
     * @var Filesystem
     */
    private $fileSystem;


    /**
     * @var AssetCacheBuilder
     */
    private $cacheBuilder;


    /**
     * AssetDumper constructor.
     *
     * @param AssetConfigurationService $configurationService
     * @param Filesystem                $fileSystem
     * @param AssetCacheBuilder         $cacheBuilder
     */
    public function __construct (AssetConfigurationService $configurationService, Filesystem $fileSystem, AssetCacheBuilder $cacheBuilder)
    {
        $this->configurationService = $configurationService;
        $this->fileSystem           = $fileSystem;
        $this->cacheBuilder         = $cacheBuilder;
    }


    /**
     * Dumps the given AssetCollection and adds it to the cache
     *
     * @param AssetCollection $assetCollection
     * @param bool            $override
     *
     * @return StatusMessage
     */
    public function dumpAssets (AssetCollection $assetCollection, $override = false)
    {
        $outputDirectory = $this->getOutputDirectory($assetCollection);

        // Create the output directory if it doesn't exist already
        if (!$this->fileSystem->exists($outputDirectory))
        {
            $this->fileSystem->mkdir($outputDirectory);
        }

        try
        {
            // Generate a unique file containing all our assets
            $outputFileName = $this->getUniqueOutputFilename($assetCollection);

            // The absolute path on the file system
            $outputFilePath = sprintf('%s/%s', $outputDirectory, $outputFileName);
            // The relative web path which is accessible to the outside
            $relativeWebPath = sprintf('%s/%s', $this->getWebPath($assetCollection), $outputFileName);

            // If this exact key-value combination is already cached we don't need to re-create it again
            if ($this->cacheBuilder->isCached($assetCollection->getIdentifier()) && !$override)
            {
                return new StatusMessage(
                    $assetCollection->getSourceTemplate(),
                    $assetCollection->getType(),
                    "Found and re-used the cached {$assetCollection->getTypeExtension()} copy for {$assetCollection->getSourceTemplate()} at {$relativeWebPath}.",
                    StatusMessage::STATUS_SUCCESS
                );
            }

            // Persist the contents
            // TODO: Think about moving this particular line into the CacheAdapter when we want to support
            // different persistence strategies.
            $this->fileSystem->dumpFile($outputFilePath, $this->getAssetsOutputContent($assetCollection));

            // Create the mapping for this collection to the accessible web path
            $this->cacheBuilder->add($assetCollection->getIdentifier(), $relativeWebPath);

            return new StatusMessage(
                $assetCollection->getSourceTemplate(),
                $assetCollection->getType(),
                "Successfully dumped {$assetCollection->getTypeExtension()} assets for {$assetCollection->getSourceTemplate()} to {$relativeWebPath}.",
                StatusMessage::STATUS_SUCCESS
            );
        }
        catch (AssetFileNotFoundException $e)
        {
            return new StatusMessage($e->getTemplatePath(), $assetCollection->getType(), $e->getMessage(), StatusMessage::STATUS_ERROR);
        }
        catch (IOException $e)
        {
            return new StatusMessage($e->getFile(), $assetCollection->getType(), $e->getMessage(), StatusMessage::STATUS_ERROR);
        }
    }


    /**
     * Generates a unique file name for the given AssetCollection which depends on the assets contents
     *
     * @param AssetCollection $assetCollection
     *
     * @return string
     *
     * @throws AssetFileNotFoundException
     */
    private function getUniqueOutputFilename (AssetCollection $assetCollection)
    {
        // Make the unique file name dependent of the contents so we'll get a new name every time the
        // contents changes, which allows us to easily prevent strong client side caching mechanism
        return sha1($this->getAssetsOutputContent($assetCollection)) . '.' . $assetCollection->getTypeExtension();
    }


    /**
     * Returns the logical output directory depending on the type of the AssetCollection
     *
     * @param AssetCollection $assetCollection
     *
     * @return string
     */
    private function getOutputDirectory (AssetCollection $assetCollection)
    {
        switch ($assetCollection->getType())
        {
            case AssetCollection::TYPE_JAVASCRIPT:
                return $this->configurationService->getLogicalJavascriptPath();

            case AssetCollection::TYPE_STYLESHEET:
                return $this->configurationService->getLogicalStylesheetPath();

            default:
                return $this->configurationService->getLogicalBasePath();
        }
    }


    /**
     * Returns the relative web path depending on the type of the AssetCollection
     *
     * @param AssetCollection $assetCollection
     *
     * @return string|null
     */
    private function getWebPath (AssetCollection $assetCollection)
    {
        switch ($assetCollection->getType())
        {
            case AssetCollection::TYPE_JAVASCRIPT:
                return $this->configurationService->getRelativeJavascriptDirectory();

            case AssetCollection::TYPE_STYLESHEET:
                return $this->configurationService->getRelativeStylesheetDirectory();

            default:
                return null;
        }
    }


    /**
     * Returns the concatenated contents for the given assets
     *
     * @param AssetCollection $assetCollection
     *
     * @return string
     *
     * @throws AssetFileNotFoundException
     */
    public function getAssetsOutputContent (AssetCollection $assetCollection)
    {
        $contents = "/* Becklyn Assets Dumper: Template {$assetCollection->getSourceTemplate()} */\n";

        foreach ($assetCollection->getAssets() as $asset)
        {
            if (!$this->fileSystem->exists($asset->getFilePath()))
            {
                throw new AssetFileNotFoundException(
                    "Unable to access or find {$assetCollection->getTypeExtension()} asset reference '{$asset->getFilePath()}'.",
                    $assetCollection->getSourceTemplate()
                );
            }

            $contents .= "/* Reference: {$asset->getTemplateReference()} --- File: {$asset->getFilePath()} */\n";
            $contents .= file_get_contents($asset->getFilePath()) . "\n";
        }

        return $contents;
    }
}
