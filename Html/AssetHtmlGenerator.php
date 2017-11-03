<?php

namespace Becklyn\AssetsBundle\Html;


use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Symfony\Component\Asset\Packages;


class AssetHtmlGenerator
{
    const TYPE_CSS = "css";
    const TYPE_JAVASCRIPT = "js";

    /**
     * @var AssetsCache
     */
    private $cache;


    /**
     * @var Packages
     */
    private $packages;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @param AssetsCache $cache
     * @param Packages    $packages
     * @param bool        $isDebug
     */
    public function __construct (AssetsCache $cache, Packages $packages, bool $isDebug)
    {
        $this->cache = $cache;
        $this->packages = $packages;
        $this->isDebug = $isDebug;
    }


    /**
     *
     * @param string   $type
     * @param string[] $assetPaths
     *
     * @throws AssetsException
     */
    public function linkAssets (string $type, array $assetPaths) : string
    {
        switch ($type)
        {
            case self::TYPE_JAVASCRIPT:
                return $this->isDebug
                    ? $this->linkDebugJavaScript($assetPaths)
                    : $this->linkProductionJavaScript($assetPaths);

            case self::TYPE_CSS:
                return $this->isDebug
                    ? $this->linkDebugCss($assetPaths)
                    : $this->linkProductionCss($assetPaths);

            default:
                throw new AssetsException(sprintf(
                    "Invalid asset type: %s",
                    $type
                ));
        }
    }


    /**
     * Links debug JavaScript
     *
     * @param string[] $assetPaths
     * @return string
     */
    private function linkDebugJavaScript (array $assetPaths) : string
    {
        $html = "";

        foreach ($assetPaths as $assetPath)
        {
            $html .= sprintf(
                '<script src="%s"></script>',
                $this->packages->getUrl($assetPath)
            );
        }

        return $html;
    }


    /**
     * Generates HTML to link to JavaScript files
     *
     * @param string[] $assetPaths
     * @return string
     * @throws AssetsException
     */
    private function linkProductionJavaScript (array $assetPaths) : string
    {
        $html = "";

        foreach ($this->fetchAssets($assetPaths) as $assetPath => $asset)
        {
            $html .= sprintf(
                '<script src="%s" integrity="%s"></script>',
                $this->packages->getUrl($asset->getOutputFilePath()),
                "sha256-{$asset->getDigest()}"
            );
        }

        return $html;
    }


    /**
     * Links debug CSS
     *
     * @param string[] $assetPaths
     * @return string
     */
    private function linkDebugCss (array $assetPaths) : string
    {
        $html = "";

        foreach ($assetPaths as $assetPath)
        {
            $html .= sprintf(
                '<link rel="stylesheet" href="%s">',
                $this->packages->getUrl($assetPath)
            );
        }

        return $html;
    }


    /**
     * Generates HTML to link to CSS files
     *
     * @param string[] $assetPaths
     * @return string
     * @throws AssetsException
     */
    private function linkProductionCss (array $assetPaths) : string
    {
        $html = "";

        foreach ($this->fetchAssets($assetPaths) as $assetPath => $asset)
        {
            $html .= sprintf(
                '<link href="%s" integrity="%s" rel="stylesheet">',
                $this->packages->getUrl($asset->getOutputFilePath()),
                "sha256-{$asset->getDigest()}"
            );
        }

        return $html;
    }


    /**
     * Fetches assets for the given asset paths
     *
     * @param string[] $assetPaths
     * @return Asset[]
     * @throws AssetsException
     */
    private function fetchAssets (array $assetPaths) : array
    {
        $assets = [];

        foreach ($assetPaths as $assetPath)
        {
            $assets[$assetPath] = $this->cache->get($assetPath);
        }

        return $assets;
    }
}
