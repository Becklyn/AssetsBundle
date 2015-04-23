<?php


namespace Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Twig\TokenParser\JavaScriptsTokenParser;
use Becklyn\AssetsBundle\Twig\TokenParser\StylesheetsTokenParser;
use Becklyn\RadBundle\Service\AbstractTwigExtension;

class AssetsTwigExtension extends AbstractTwigExtension
{
    /**
     * @inheritdoc
     */
    public function getTokenParsers ()
    {
        $cacheBuilder = $this->container->get('becklyn.assets.cache.cache_builder');

        // Add our very own Token Parser to the compiler pipeline
        return [
            new JavaScriptsTokenParser($cacheBuilder),
            new StylesheetsTokenParser($cacheBuilder),
        ];
    }
}
