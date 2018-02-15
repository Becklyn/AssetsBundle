<?php

namespace Becklyn\AssetsBundle\Url;


use Becklyn\AssetsBundle\Asset\AssetsRegistry;
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
                // Instead of throwing an exception since we can't resolve the asset, we're returning the
                // asset path un-altered so the browser will resolve it to a 404
                return $assetPath;
            }
        }

        return $this->router->generate("becklyn_assets_embed", [
            "path" => \rawurlencode($assetPath),
        ]);
    }
}
