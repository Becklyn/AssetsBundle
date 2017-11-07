<?php

namespace Becklyn\AssetsBundle\Html;


use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Symfony\Component\Asset\Packages;


class AssetHtmlGenerator
{
    const TYPE_CSS = "css";
    const TYPE_JAVASCRIPT = "js";


    /**
     * @var AssetsRegistry
     */
    private $registry;


    /**
     * @var Packages
     */
    private $packages;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @param AssetsRegistry $registry
     * @param Packages       $packages
     * @param bool           $isDebug
     */
    public function __construct (AssetsRegistry $registry, Packages $packages, bool $isDebug)
    {
        $this->registry = $registry;
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
                return $this->linkJavaScript($assetPaths);

            case self::TYPE_CSS:
                return $this->linkCss($assetPaths);

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
     * @throws AssetsException
     */
    private function linkJavaScript (array $assetPaths) : string
    {
        $html = "";

        foreach ($assetPaths as $assetPath)
        {
            $html .= sprintf(
                '<script src="%s"%s></script>',
                $this->getAssetUrlPath($assetPath),
                $this->getIntegrityHtml($assetPath)
            );
        }

        return $html;
    }


    /**
     * Links debug CSS
     *
     * @param string[] $assetPaths
     * @return string
     * @throws AssetsException
     */
    private function linkCss (array $assetPaths) : string
    {
        $html = "";

        foreach ($assetPaths as $assetPath)
        {
            $html .= sprintf(
                '<link rel="stylesheet" href="%s"%s>',
                $this->getAssetUrlPath($assetPath),
                $this->getIntegrityHtml($assetPath)
            );
        }

        return $html;
    }


    /**
     * Returns the asset url
     *
     * @param string $assetPath
     * @return string
     * @throws AssetsException
     */
    public function getAssetUrlPath (string $assetPath) : string
    {
        $path = $this->isDebug
            ? $assetPath
            : $this->registry->get($assetPath)->getOutputFilePath();

        return $this->packages->getUrl($path);
    }


    /**
     * Returns the integrity HTML snippet
     *
     * @param string $assetPath
     * @return string
     * @throws AssetsException
     */
    private function getIntegrityHtml (string $assetPath) : string
    {
        return $this->isDebug
            ? ""
            : sprintf(
                ' integrity="sha256-%s"',
                $this->registry->get($assetPath)->getDigest()
            );
    }
}
