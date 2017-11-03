<?php

namespace Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Exception\AssetsException;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Generates the asset instances
 */
class AssetGenerator
{
    /**
     * @var string
     */
    private $webPath;


    /**
     * @var string
     */
    private $outputDir;


    /**
     * @var Filesystem
     */
    private $filesystem;


    /**
     * @param string $projectDir
     * @param string $outputDir     the output dir relative to the public/ directory
     */
    public function __construct (string $projectDir, string $outputDir = "assets")
    {
        $this->webPath = "{$projectDir}/public";
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
        $filePath = "{$this->webPath}/" . ltrim($assetPath, "/");

        if (!\is_file($filePath))
        {
            throw new AssetsException(sprintf(
                "Missing assets file: %s",
                $assetPath
            ));
        }

        $hash = \base64_encode(\hash_file("sha256", $filePath, true));
        $asset = new Asset($this->getOutputDirectory($assetPath), $filePath, $hash);

        $outputPath = "{$this->webPath}/{$asset->getOutputFilePath()}";

        // ensure that the target directory exists
        $this->filesystem->mkdir(dirname($outputPath));

        // copy file
        $this->filesystem->copy($filePath, $outputPath);

        return $asset;
    }


    /**
     * Generates the output directory
     *
     * @param string $assetPath
     * @return string
     */
    private function getOutputDirectory (string $assetPath) : string
    {
        $assetPath = ltrim($assetPath, "/");
        $assetPath = dirname($assetPath);

        if ("bundles/" === \substr($assetPath, 0, 8))
        {
            $assetPath = \substr($assetPath, 8);
        }

        return "{$this->outputDir}/{$assetPath}";
    }


    /**
     * Removes all generated files
     */
    public function removeAllGeneratedFiles () : void
    {
        $this->filesystem->remove("{$this->webPath}/{$this->outputDir}");
    }
}
