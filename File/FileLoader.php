<?php

namespace Becklyn\AssetsBundle\File;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Exception\FileNotFoundException;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;


class FileLoader
{
    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @var NamespaceRegistry
     */
    private $namespaceRegistry;


    /**
     * @var FileTypeRegistry
     */
    private $fileTypeRegistry;


    /**
     * @param bool              $isDebug
     * @param NamespaceRegistry $namespaceRegistry
     * @param FileTypeRegistry  $fileTypeRegistry
     */
    public function __construct (bool $isDebug, NamespaceRegistry $namespaceRegistry, FileTypeRegistry $fileTypeRegistry)
    {
        $this->isDebug = $isDebug;
        $this->namespaceRegistry = $namespaceRegistry;
        $this->fileTypeRegistry = $fileTypeRegistry;
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

        $fileType = $this->fileTypeRegistry->getFileType($asset);

        // prepend file header in dev and process in prod
        $fileContent = $this->isDebug
            ? $fileType->prependFileHeader($asset, $filePath, $fileContent)
            : $fileType->processForProd($asset, $fileContent);

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
