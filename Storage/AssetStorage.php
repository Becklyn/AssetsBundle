<?php

namespace Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Becklyn\AssetsBundle\Processor\ProcessorRegistry;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Generates the asset instances
 */
class AssetStorage
{
    /**
     * @var ProcessorRegistry
     */
    private $processorRegistry;


    /**
     * @var NamespaceRegistry
     */
    private $namespaceRegistry;


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
     * @param ProcessorRegistry $processorRegistry
     * @param NamespaceRegistry $namespaceRegistry
     * @param string            $publicPath the absolute path to the public/ (or web/) directory
     * @param string            $outputDir  the output dir relative to the public/ directory
     */
    public function __construct (
        ProcessorRegistry $processorRegistry,
        NamespaceRegistry $namespaceRegistry,
        string $publicPath,
        string $outputDir
    )
    {
        $this->processorRegistry = $processorRegistry;
        $this->namespaceRegistry = $namespaceRegistry;
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
        $filePath = $this->namespaceRegistry->getFilePath($asset);

        if (!\is_file($filePath))
        {
            throw new AssetsException(sprintf(
                "Missing assets file: %s",
                $asset->getAssetPath()
            ));
        }

        $processor = $this->processorRegistry->get($asset);
        $fileContent = \file_get_contents($filePath);

        if (null !== $processor)
        {
            $fileContent = $processor->process($asset, $fileContent);
        }

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
     * Removes all generated files
     */
    public function removeAllGeneratedFiles () : void
    {
        $this->filesystem->remove("{$this->publicPath}/{$this->outputDir}");
    }
}
