<?php

namespace Becklyn\AssetsBundle\Storage;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\File\FileLoader;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Generates the asset instances
 */
class AssetStorage
{
    /**
     * @var FileLoader
     */
    private $fileLoader;


    /**
     * @var string
     */
    private $publicPath;


    /**
     * @var string
     */
    private $outputDir;


    /**
     * @var Filesystem
     */
    private $filesystem;


    /**
     * @param FileLoader $fileLoader
     * @param string     $publicPath the absolute path to the public/ (or web/) directory
     * @param string     $outputDir  the output dir relative to the public/ directory
     */
    public function __construct (
        FileLoader $fileLoader,
        string $publicPath,
        string $outputDir
    )
    {
        $this->fileLoader = $fileLoader;
        $this->publicPath = rtrim($publicPath, "/");
        $this->outputDir = trim($outputDir, "/");
        $this->filesystem = new Filesystem();
    }


    /**
     * Imports the given asset
     *
     * @param string $assetPath
     * @return Asset
     * @throws AssetsException
     */
    public function import (Asset $asset) : Asset
    {
        $fileContent = $this->fileLoader->loadFile($asset, FileLoader::MODE_PROD);

        $asset->setHash(
            \base64_encode(\hash("sha256", $fileContent, true))
        );

        $outputPath = "{$this->publicPath}/{$asset->getDumpFilePath()}";

        // ensure that the target directory exists
        $this->filesystem->mkdir(dirname($outputPath));

        // copy file
        $this->filesystem->dumpFile($outputPath, $fileContent);

        return $asset;
    }


    /**
     * Removes all stored files
     */
    public function removeAllStoredFiles () : void
    {
        $this->filesystem->remove("{$this->publicPath}/{$this->outputDir}");
    }
}
