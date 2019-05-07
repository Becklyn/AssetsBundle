<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Exception\FileNotFoundException;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;

class FileLoader
{
    const MODE_PROD = true;
    const MODE_DEV = false;
    const MODE_UNTOUCHED = null;

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
    public function __construct (NamespaceRegistry $namespaceRegistry, FileTypeRegistry $fileTypeRegistry)
    {
        $this->namespaceRegistry = $namespaceRegistry;
        $this->fileTypeRegistry = $fileTypeRegistry;
    }


    /**
     * Loads an asset's file content.
     *
     * @param Asset $asset
     * @param bool  $mode  one of the MODE_* constants
     *
     * @throws AssetsException
     * @throws FileNotFoundException
     *
     * @return string
     */
    public function loadFile (Asset $asset, ?bool $mode) : string
    {
        $filePath = $this->getFilePath($asset);

        if (!\is_file($filePath))
        {
            throw new FileNotFoundException(\sprintf(
                "Asset '%s' not found at '%s'.",
                $asset->getAssetPath(),
                $filePath
            ));
        }

        $fileContent = \file_get_contents($filePath);

        if (false === $fileContent)
        {
            throw new FileNotFoundException(\sprintf(
                "Can't read asset file '%s' at '%s'.",
                $asset->getAssetPath(),
                $filePath
            ));
        }

        $fileType = $this->fileTypeRegistry->getFileType($asset);

        if (self::MODE_UNTOUCHED !== $mode)
        {
            // prepend file header in dev and process in prod
            $fileContent = (self::MODE_PROD === $mode)
                ? $fileType->processForProd($asset, $fileContent)
                : $fileType->processForDev($asset, $filePath, $fileContent);
        }

        return $fileContent;
    }


    /**
     * Returns the file path for the given asset.
     *
     * @param Asset $asset
     *
     * @throws AssetsException
     *
     * @return string
     */
    public function getFilePath (Asset $asset)
    {
        return $this->namespaceRegistry->getFilePath($asset);
    }
}
