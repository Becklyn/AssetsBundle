<?php

namespace Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Helper\AssetHelper;
use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;


class AssetsTwigExtension extends \Twig_Extension
{
    /**
     * @var AssetHtmlGenerator
     */
    private $htmlReferences;


    /**
     * @var AssetHelper
     */
    private $assetHelper;


    /**
     *
     * @param AssetHtmlGenerator $htmlReferences
     * @param AssetHelper        $assetHelper
     */
    public function __construct (
        AssetHtmlGenerator $htmlReferences,
        AssetHelper $assetHelper
    )
    {
        $this->htmlReferences = $htmlReferences;
        $this->assetHelper = $assetHelper;
    }


    /**
     * @inheritdoc
     */
    public function getFunctions ()
    {
        return [
            new \Twig_SimpleFunction("asset", [$this->assetHelper, "getUrl"]),
            new \Twig_SimpleFunction("asset_inline", [$this->assetHelper, "embed"], ["is_safe" => ["html"]]),
            new \Twig_SimpleFunction("assets_link", [$this->htmlReferences, "linkAssets"], ["is_safe" => ["html"]]),
        ];
    }
}
