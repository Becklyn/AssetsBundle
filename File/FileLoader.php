<?php

namespace Becklyn\AssetsBundle\File;


use Becklyn\AssetsBundle\Asset\NamespacedAsset;
use Becklyn\AssetsBundle\Entry\EntryNamespaces;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Exception\FileNotFoundException;
use Becklyn\AssetsBundle\Processor\ProcessorRegistry;


class FileLoader
{
    /**
     * @var EntryNamespaces
     */
    private $entryNamespaces;


    /**
     * @var ProcessorRegistry
     */
    private $processorRegistry;


    /**
     *
     * @param EntryNamespaces   $entryNamespaces
     * @param ProcessorRegistry $processorRegistry
     */
    public function __construct (EntryNamespaces $entryNamespaces, ProcessorRegistry $processorRegistry)
    {
        $this->entryNamespaces = $entryNamespaces;
        $this->processorRegistry = $processorRegistry;
    }


    /**
     * Loads an asset's file content
     *
     * @param string $assetPath
     * @throws AssetsException
     *
     * @return string
     */
    public function loadFile (string $assetPath) : string
    {
        $filePath = $this->getFilePath($assetPath);
        $processor = $this->processorRegistry->get($filePath);

        if (!\is_file($filePath))
        {
            throw new FileNotFoundException(sprintf(
                "Asset not found at '%s'.",
                $filePath
            ));
        }

        $fileContent = \file_get_contents($filePath);

        if (false === $fileContent)
        {
            throw new FileNotFoundException(sprintf(
                "Can't read asset file '%s' at '%s'.",
                $assetPath,
                $filePath
            ));
        }

        if (null !== $processor)
        {
            $fileContent = $processor->process($assetPath, $fileContent);
        }

        return $fileContent;
    }


    /**
     * Returns the file path for the given asset
     *
     * @param string $assetPath
     * @return string
     *
     * @throws AssetsException
     */
    public function getFilePath (string $assetPath)
    {
        $asset = NamespacedAsset::createFromFullPath($assetPath);
        return $this->entryNamespaces->getFilePath($asset);
    }
}
