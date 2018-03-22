<?php

namespace Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;
use Becklyn\AssetsBundle\File\FileLoader;
use Becklyn\AssetsBundle\Url\AssetUrl;


class AssetsTwigExtension extends \Twig_Extension
{
    /**
     * @var AssetHtmlGenerator
     */
    private $htmlReferences;


    /**
     * @var AssetUrl
     */
    private $assetUrl;


    /**
     * @var FileLoader
     */
    private $fileLoader;


    /**
     *
     * @param AssetHtmlGenerator $htmlReferences
     * @param FileLoader         $fileLoader
     */
    public function __construct (AssetHtmlGenerator $htmlReferences, AssetUrl $assetUrl, FileLoader $fileLoader)
    {
        $this->htmlReferences = $htmlReferences;
        $this->assetUrl = $assetUrl;
        $this->fileLoader = $fileLoader;
    }


    /**
     * @param array $assetPaths
     * @return string
     * @throws \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function linkAssets (array $assetPaths) : string
    {
        return $this->htmlReferences->linkAssets($assetPaths);
    }


    /**
     * Inlines the given asset
     *
     * @param array $assetPaths
     * @return string
     * @throws \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function inlineAsset (string $assetPath) : string
    {
        $asset = Asset::createFromAssetPath($assetPath);
        return $this->fileLoader->loadFile($asset, FileLoader::MODE_UNTOUCHED);
    }


    /**
     * @inheritdoc
     */
    public function getFunctions ()
    {
        return [
            new \Twig_SimpleFunction("asset", [$this->assetUrl, "generateUrlFromAssetPath"]),
            new \Twig_SimpleFunction("asset_inline", [$this, "inlineAsset"], ["is_safe" => ["html"]]),
            new \Twig_SimpleFunction("assets_link", [$this, "linkAssets"], ["is_safe" => ["html"]]),
        ];
    }
}
