<?php

namespace Becklyn\AssetsBundle\Html;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Dependency\DependencyMap;
use Becklyn\AssetsBundle\Dependency\DependencyMapFactory;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\File\FileTypeRegistry;
use Becklyn\AssetsBundle\Url\AssetUrl;


class AssetHtmlGenerator
{
    /**
     * @var AssetsRegistry
     */
    private $registry;


    /**
     * @var AssetUrl
     */
    private $assetUrl;


    /**
     * @var FileTypeRegistry
     */
    private $fileTypeRegistry;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @var DependencyMap
     */
    private $dependencyMap;


    /**
     *
     * @param AssetsRegistry       $registry
     * @param AssetUrl             $assetUrl
     * @param FileTypeRegistry     $fileTypeRegistry
     * @param bool                 $isDebug
     * @param DependencyMapFactory $dependencyMapFactory
     */
    public function __construct (
        AssetsRegistry $registry,
        AssetUrl $assetUrl,
        FileTypeRegistry $fileTypeRegistry,
        bool $isDebug,
        DependencyMapFactory $dependencyMapFactory
    )
    {
        $this->registry = $registry;
        $this->assetUrl = $assetUrl;
        $this->fileTypeRegistry = $fileTypeRegistry;
        $this->isDebug = $isDebug;
        $this->dependencyMap = $dependencyMapFactory->getDependencyMap();
    }


    /**
     *
     * @param string[] $assetPaths
     *
     * @throws AssetsException
     */
    public function linkAssets (array $assetPaths, bool $withDependencies) : string
    {
        if ($withDependencies)
        {
            $assetPaths = $this->dependencyMap->getImportsWithDependencies($assetPaths);
        }

        /** @var Asset[] $assets */
        $assets = \array_map([Asset::class, "createFromAssetPath"], $assetPaths);
        $html = "";

        foreach ($assets as $asset)
        {
            $fileType = $this->fileTypeRegistry->getFileType($asset);
            $htmlLinkFormat = $fileType->getHtmlLinkFormat();

            if (null === $htmlLinkFormat)
            {
                throw new AssetsException(sprintf(
                    "No HTML link format found for file of type: %s",
                    $asset->getFileType()
                ));
            }

            $html .= sprintf(
                $htmlLinkFormat,
                $this->assetUrl->generateUrl($asset),
                $this->isDebug ? "" : $this->getIntegrityHtml($asset)
            );
        }

        return $html;
    }


    /**
     * Returns the integrity HTML snippet
     *
     * @param Asset $asset
     * @return string
     * @throws AssetsException
     */
    private function getIntegrityHtml (Asset $asset) : string
    {
        return $this->isDebug
            ? ""
            : sprintf(
                ' integrity="sha256-%s"',
                $this->registry->get($asset)->getHash()
            );
    }
}
