<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\RouteLoader;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Controller\EmbedController;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class AssetsRouteLoader extends Loader
{
    public const ROUTE_NAME = "becklyn_assets.embed";

    private string $outputDir;


    public function __construct (string $outputDir)
    {
        parent::__construct();

        $this->outputDir = $outputDir;
    }


    /**
     * @inheritDoc
     */
    public function load ($resource, $type = null) : RouteCollection
    {
        $collection = new RouteCollection();

        $route = new Route(
            "/{$this->outputDir}/{namespace}/{path}",
            [
                "_controller" => EmbedController::class . "::embed",
            ],
            [
                "namespace" => Asset::NAMESPACE_REGEX,
                "path" => ".*?",
            ]
        );

        $collection->add(self::ROUTE_NAME, $route);

        return $collection;
    }


    /**
     * @inheritdoc
     */
    public function supports ($resource, $type = null) : bool
    {
        return "becklyn-assets" === $type;
    }
}
