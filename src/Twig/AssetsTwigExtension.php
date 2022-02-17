<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Helper\AssetHelper;
use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetsTwigExtension extends AbstractExtension
{
    private AssetHtmlGenerator $htmlReferences;
    private AssetHelper $assetHelper;


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
    public function getFunctions () : array
    {
        return [
            new TwigFunction("asset", [$this->assetHelper, "getUrl"]),
            new TwigFunction("asset_inline", [$this->assetHelper, "embed"], ["is_safe" => ["html"]]),
            new TwigFunction("assets_link", [$this->htmlReferences, "linkAssets"], ["is_safe" => ["html"]]),
        ];
    }
}
