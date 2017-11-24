<?php

namespace Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Entry\EntryNamespaces;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Processor\ProcessorRegistry;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Generates the asset instances
 */
class AssetGenerator
{
    /**
     * @var ProcessorRegistry
     */
    private $processorRegistry;


    /**
     * @var EntryNamespaces
     */
    private $entryNamespaces;


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
     * @param EntryNamespaces   $entryNamespaces
     * @param string            $publicPath the absolute path to the public/ (or web/) directory
     * @param string            $outputDir  the output dir relative to the public/ directory
     */
    public function __construct (ProcessorRegistry $processorRegistry, EntryNamespaces $entryNamespaces, string $publicPath, string $outputDir)
    {
        $this->processorRegistry = $processorRegistry;
        $this->entryNamespaces = $entryNamespaces;
        $this->publicPath = rtrim($publicPath, "/");
        $this->outputDir = trim($outputDir, "/");
        $this->filesystem = new Filesystem();
    }


    /**
     * @param string $assetPath
     * @return Asset
     * @throws AssetsException
     */
    public function generateAsset (string $assetPath) : Asset
    {
        $namespacedAsset = NamespacedAsset::createFromFullPath($assetPath);
        $filePath = $this->entryNamespaces->getFilePath($namespacedAsset);

        if (!\is_file($filePath))
        {
            throw new AssetsException(sprintf(
                "Missing assets file: %s",
                $assetPath
            ));
        }

        $processor = $this->processorRegistry->get($assetPath);
        $fileContent = \file_get_contents($filePath);

        if (null !== $processor)
        {
            $fileContent = $processor->process($assetPath, $fileContent);
        }

        $hash = \base64_encode(\hash("sha256", $fileContent, true));
        $asset = new Asset($this->getOutputDirectory($namespacedAsset), $filePath, $hash);

        $outputPath = "{$this->publicPath}/{$asset->getOutputFilePath()}";

        // ensure that the target directory exists
        $this->filesystem->mkdir(dirname($outputPath));

        // copy file
        $this->filesystem->dumpFile($outputPath, $fileContent);

        return $asset;
    }


    /**
     * Generates the output directory
     *
     * @param string $assetPath
     * @return string
     */
    private function getOutputDirectory (NamespacedAsset $asset) : string
    {
        $outputDirectory = "{$this->outputDir}/{$asset->getNamespace()}";
        $dir = dirname($asset->getPath());

        if ("." !== $dir)
        {
            $outputDirectory .= "/{$dir}";
        }

        return $outputDirectory;
    }


    /**
     * Removes all generated files
     */
    public function removeAllGeneratedFiles () : void
    {
        $this->filesystem->remove("{$this->publicPath}/{$this->outputDir}");
    }
}
