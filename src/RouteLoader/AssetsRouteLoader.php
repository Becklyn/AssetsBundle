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

    /**
     * @var string
     */
    private $outputDir;


    /**
     */
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
    public function supports ($resource, $type = null)
    {
        return "becklyn-assets" === $type;
    }
}
