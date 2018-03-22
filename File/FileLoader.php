<?php

namespace Becklyn\AssetsBundle\File;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Exception\FileNotFoundException;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Becklyn\AssetsBundle\Processor\ProcessorRegistry;


class FileLoader
{
    /**
     * @var NamespaceRegistry
     */
    private $namespaceRegistry;


    /**
     * @var ProcessorRegistry
     */
    private $processorRegistry;


    /**
     * @param NamespaceRegistry $namespaceRegistry
     * @param ProcessorRegistry $processorRegistry
     */
    public function __construct (NamespaceRegistry $namespaceRegistry, ProcessorRegistry $processorRegistry)
    {
        $this->namespaceRegistry = $namespaceRegistry;
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
    public function loadFile (Asset $asset) : string
    {
        $filePath = $this->getFilePath($asset);
        $processor = $this->processorRegistry->get($asset);

        if (!\is_file($filePath))
        {
            throw new FileNotFoundException(sprintf(
                "Asset '%s' not found at '%s'.",
                $asset->getAssetPath(),
                $filePath
            ));
        }

        $fileContent = \file_get_contents($filePath);

        if (false === $fileContent)
        {
            throw new FileNotFoundException(sprintf(
                "Can't read asset file '%s' at '%s'.",
                $asset->getAssetPath(),
                $filePath
            ));
        }

        if (null !== $processor)
        {
            $fileContent = $processor->process($asset, $fileContent);
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
    public function getFilePath (Asset $asset)
    {
        return $this->namespaceRegistry->getFilePath($asset);
    }
}
