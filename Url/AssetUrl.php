<?php

namespace Becklyn\AssetsBundle\Url;


use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Asset\NamespacedAsset;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;


class AssetUrl
{
    /**
     * @var AssetsRegistry
     */
    private $registry;


    /**
     * @var RouterInterface
     */
    private $router;


    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     *
     * @param AssetsRegistry  $registry
     * @param RouterInterface $router
     * @param RequestStack    $requestStack
     * @param bool            $isDebug
     */
    public function __construct (AssetsRegistry $registry, RouterInterface $router, RequestStack $requestStack, bool $isDebug)
    {
        $this->registry = $registry;
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->isDebug = $isDebug;
    }


    /**
     * @param string $assetPath
     * @return string
     * @throws AssetsException
     */
    public function generateUrl (string $assetPath) : string
    {
        if (!$this->isDebug)
        {
            $request = $this->requestStack->getMasterRequest();

            if (null === $request)
            {
                throw new AssetsException(sprintf(
                    "Can't embed asset '%s' without request.",
                    $assetPath
                ));
            }

            try
            {
                return "{$request->getBaseUrl()}/{$this->registry->get($assetPath)->getOutputFilePath()}";
            }
            catch (AssetsException $e)
            {
                // In debug we want to let the developer know that there's a bug due to a missing asset
                // so we just re-throw the exception.
                if ($this->isDebug)
                {
                    throw $e;
                }

                // In prod we don't want to potentially bring down the entire since we can't resolve an asset,
                // so we're returning the asset path un-altered so the browser can resolve it to a 404
                return $assetPath;
            }
        }

        $asset = NamespacedAsset::createFromFullPath($assetPath);
        return $this->router->generate("becklyn_assets_embed", [
            "namespace" => $asset->getNamespace(),
            "path" => $asset->getPath(),
        ]);
    }
}
