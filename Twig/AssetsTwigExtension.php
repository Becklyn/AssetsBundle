<?php

namespace Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;


class AssetsTwigExtension extends \Twig_Extension
{
    /**
     * @var AssetHtmlGenerator
     */
    private $htmlReferences;


    /**
     * @param AssetHtmlGenerator $htmlReferences
     */
    public function __construct (AssetHtmlGenerator $htmlReferences)
    {
        $this->htmlReferences = $htmlReferences;
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
     * @inheritdoc
     */
    public function getFunctions ()
    {
        return [
            new \Twig_Function("cssAssets", [$this, "cssAssets"], ["is_safe" => ["html"]]),
            new \Twig_Function("jsAssets", [$this, "jsAssets"], ["is_safe" => ["html"]]),
            new \Twig_Function("assetPath", [$this, "getAssetUrlPath"]),
        ];
    }
}
