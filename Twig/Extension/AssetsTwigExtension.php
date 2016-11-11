<?php

namespace Becklyn\AssetsBundle\Twig\Extension;

use Becklyn\AssetsBundle\Twig\Extension\TokenParser\JavaScriptsTokenParser;
use Becklyn\AssetsBundle\Twig\Extension\TokenParser\StylesheetsTokenParser;


class AssetsTwigExtension extends \Twig_Extension
{
    /**
     * @inheritdoc
     */
    public function getTokenParsers ()
    {
        return [
            new JavaScriptsTokenParser(),
            new StylesheetsTokenParser(),
        ];
    }
}
