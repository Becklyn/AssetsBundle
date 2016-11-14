<?php

namespace Becklyn\AssetsBundle\Twig\Extension;

use Becklyn\AssetsBundle\Path\PathGenerator;
use Becklyn\AssetsBundle\Twig\Extension\TokenParser\JavaScriptsTokenParser;
use Becklyn\AssetsBundle\Twig\Extension\TokenParser\StylesheetsTokenParser;


class AssetsTwigExtension extends \Twig_Extension
{
    /**
     * @var PathGenerator
     */
    private $pathGenerator;



    /**
     * @param PathGenerator $pathGenerator
     */
    public function __construct (PathGenerator $pathGenerator)
    {
        $this->pathGenerator = $pathGenerator;
    }

    /**
     * @inheritdoc
     */
    public function getTokenParsers ()
    {
        return [
            new JavaScriptsTokenParser($this->pathGenerator),
            new StylesheetsTokenParser($this->pathGenerator),
        ];
    }
}
