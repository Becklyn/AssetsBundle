<?php

namespace Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Twig\TokenParser\JavaScriptsTokenParser;
use Becklyn\AssetsBundle\Twig\TokenParser\StylesheetsTokenParser;
use Symfony\Component\DependencyInjection\ContainerInterface;


class AssetsTwigExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * AppTwigExtension constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct (ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * @inheritdoc
     */
    public function getTokenParsers ()
    {
        $assetCache = $this->container->get('becklyn.assets.cache');

        // Add our very own Token Parser to the compiler pipeline
        return [
            new JavaScriptsTokenParser($assetCache),
            new StylesheetsTokenParser($assetCache),
        ];
    }
}
