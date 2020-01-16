<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Url;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Exception\FileNotFoundException;
use Becklyn\AssetsBundle\File\FileLoader;
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
     * @var FileLoader
     */
    private $fileLoader;


    /**
     */
    public function __construct (AssetsRegistry $assetsRegistry, RouterInterface $router, FileLoader $fileLoader, bool $isDebug, ?LoggerInterface $logger)
    {
        $this->assetsRegistry = $assetsRegistry;
        $this->router = $router;
        $this->isDebug = $isDebug;
        $this->logger = $logger;
        $this->fileLoader = $fileLoader;
    }


    /**
     * @throws AssetsException
     */
    public function generateUrl (Asset $asset) : string
    {
        $filePath = $asset->getFilePath();

        try
        {
            if ($this->isDebug)
            {
                // in debug only check that the file exists, so we can eagerly throw an exception
                if (!$this->fileLoader->fileForAssetExists($asset))
                {
                    throw new FileNotFoundException(\sprintf(
                        "Asset '%s' not found at '%s'.",
                        $asset->getAssetPath(),
                        $this->fileLoader->getFilePath($asset)
                    ));
                }
            }
            else
            {
                // Only actually load the asset in production due to the importing of files being very expensive.
                // Be aware that you will only catch missing assets in your browser dev tools 404 errors.
                $cachedAsset = $this->assetsRegistry->get($asset);

                // use dumped file path in prod
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
