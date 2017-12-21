<?php

namespace Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;
use Becklyn\AssetsBundle\Loader\FileLoader;


class AssetsTwigExtension extends \Twig_Extension
{
    /**
     * @var AssetHtmlGenerator
     */
    private $htmlReferences;


    /**
     * @var FileLoader
     */
    private $fileLoader;


    /**
     *
     * @param AssetHtmlGenerator $htmlReferences
     * @param FileLoader         $fileLoader
     */
    public function __construct (AssetHtmlGenerator $htmlReferences, FileLoader $fileLoader)
    {
        $this->htmlReferences = $htmlReferences;
        $this->fileLoader = $fileLoader;
    }


    /**
     * @param array $assetPaths
     * @return string
     * @throws \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function cssAssets (array $assetPaths) : string
    {
        return $this->htmlReferences->linkAssets(AssetHtmlGenerator::TYPE_CSS, $assetPaths);
    }


    /**
     * @param array $assetPaths
     * @return string
     * @throws \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function jsAssets (array $assetPaths) : string
    {
        return $this->htmlReferences->linkAssets(AssetHtmlGenerator::TYPE_JAVASCRIPT, $assetPaths);
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
        return $this->fileLoader->loadFile($assetPath);
    }


    /**
     * @inheritdoc
     */
    public function getFunctions ()
    {
        return [
            new \Twig_SimpleFunction("asset", [$this->htmlReferences, "getAssetUrlPath"]),
            new \Twig_SimpleFunction("asset_inline", [$this, "inlineAsset"], ["is_safe" => ["html"]]),
            new \Twig_SimpleFunction("assets_css", [$this, "cssAssets"], ["is_safe" => ["html"]]),
            new \Twig_SimpleFunction("assets_js", [$this, "jsAssets"], ["is_safe" => ["html"]]),
        ];
    }
}
