<?php

namespace Becklyn\AssetsBundle\RouteLoader;

use Becklyn\AssetsBundle\Asset\NamespacedAsset;
use Becklyn\AssetsBundle\Controller\EmbedController;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


class AssetsRouteLoader extends Loader
{
    private $outputDir;

    public function __construct (string $outputDir)
    {
        $this->outputDir = $outputDir;
    }


    /**
     * @inheritdoc
     */
    public function load ($resource, $type = null)
    {
        $collection = new RouteCollection();

        $route = new Route(
            "/{$this->outputDir}/{namespace}/{path}",
            [
                "_controller" => EmbedController::class . "::embed",
            ],
            [
                "namespace" => NamespacedAsset::NAMESPACE_REGEX,
                "path" => ".*?"
            ]
        );

        $collection->add("becklyn_assets_embed", $route);

        return $collection;
    }


    /**
     * @inheritdoc
     */
    public function supports ($resource, $type = null)
    {
        return $type === "becklyn-assets";
    }
}
