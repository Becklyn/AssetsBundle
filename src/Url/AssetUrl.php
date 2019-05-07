<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Url;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\RouteLoader\AssetsRouteLoader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;

class AssetUrl
{
    /**
     * @var AssetsRegistry
     */
    private $assetsRegistry;


    /**
     * @var RouterInterface
     */
    private $router;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @var LoggerInterface|null
     */
    private $logger;


    /**
     * @param AssetsRegistry       $assetsRegistry
     * @param RouterInterface      $router
     * @param bool                 $isDebug
     * @param LoggerInterface|null $logger
     */
    public function __construct (AssetsRegistry $assetsRegistry, RouterInterface $router, bool $isDebug, ?LoggerInterface $logger)
    {
        $this->assetsRegistry = $assetsRegistry;
        $this->router = $router;
        $this->isDebug = $isDebug;
        $this->logger = $logger;
    }


    /**
     * @param Asset $asset
     *
     * @throws AssetsException
     *
     * @return string
     */
    public function generateUrl (Asset $asset) : string
    {
        $filePath = $asset->getFilePath();

        try
        {
            // always load asset to catch missing assets in dev
            $cachedAsset = $this->assetsRegistry->get($asset);

            // use dumped file path in prod
            if (!$this->isDebug)
            {
                $asset = $cachedAsset;
                $filePath = $cachedAsset->getDumpFilePath();
            }
        }
        catch (AssetsException $e)
        {
            // In debug we want to let the developer know that there's a bug due to a missing asset
            // so we just re-throw the exception.
            if ($this->isDebug)
            {
                throw $e;
            }

            if (null !== $this->logger)
            {
                // In prod we don't want to potentially bring down the entire since we can't resolve an asset,
                // so we're returning the asset path un-altered so the browser can resolve it to a 404
                // so just log the error
                $this->logger->error("Can't load asset {assetPath}", [
                    "assetPath" => $asset->getAssetPath(),
                    "asset" => $asset,
                ]);
            }
        }

        return $this->router->generate(AssetsRouteLoader::ROUTE_NAME, [
            "namespace" => $asset->getNamespace(),
            "path" => $filePath,
        ]);
    }
}
