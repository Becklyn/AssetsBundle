<?php

namespace Becklyn\AssetsBundle\Html;

use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Symfony\Component\Routing\RouterInterface;


class AssetHtmlGenerator
{
    const TYPE_CSS = "css";
    const TYPE_JAVASCRIPT = "js";


    /**
     * @var AssetsRegistry
     */
    private $registry;


    /**
     * @var RouterInterface
     */
    private $router;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @param AssetsRegistry  $registry
     * @param RouterInterface $router
     * @param bool            $isDebug
     */
    public function __construct (AssetsRegistry $registry, RouterInterface $router, bool $isDebug)
    {
        $this->registry = $registry;
        $this->router = $router;
        $this->isDebug = $isDebug;
    }


    /**
     *
     * @param string   $type
     * @param string[] $assetPaths
     *
     * @throws AssetsException
     */
    public function linkAssets (string $type, array $assetPaths) : string
    {
        switch ($type)
        {
            case self::TYPE_JAVASCRIPT:
                return $this->linkJavaScript($assetPaths);

            case self::TYPE_CSS:
                return $this->linkCss($assetPaths);

            default:
                throw new AssetsException(sprintf(
                    "Invalid asset type: %s",
                    $type
                ));
        }
    }


    /**
     * Links debug JavaScript
     *
     * @param string[] $assetPaths
     * @return string
     * @throws AssetsException
     */
    private function linkJavaScript (array $assetPaths) : string
    {
        $html = "";

        foreach ($assetPaths as $assetPath)
        {
            $html .= sprintf(
                '<script defer src="%s"%s></script>',
                $this->getAssetUrlPath($assetPath),
                $this->getIntegrityHtml($assetPath)
            );
        }

        return $html;
    }


    /**
     * Links debug CSS
     *
     * @param string[] $assetPaths
     * @return string
     * @throws AssetsException
     */
    private function linkCss (array $assetPaths) : string
    {
        $html = "";

        foreach ($assetPaths as $assetPath)
        {
            $html .= sprintf(
                '<link rel="stylesheet" href="%s"%s>',
                $this->getAssetUrlPath($assetPath),
                $this->getIntegrityHtml($assetPath)
            );
        }

        return $html;
    }


    /**
     * Returns the asset url
     *
     * @param string $assetPath
     * @return string
     * @throws AssetsException
     */
    public function getAssetUrlPath (string $assetPath) : string
    {
        if (!$this->isDebug)
        {
            return $this->registry->get($assetPath)->getOutputFilePath();
        }

        return $this->router->generate("becklyn_assets_embed", [
            "path" => \rawurlencode($assetPath),
        ]);
    }


    /**
     * Returns the integrity HTML snippet
     *
     * @param string $assetPath
     * @return string
     * @throws AssetsException
     */
    private function getIntegrityHtml (string $assetPath) : string
    {
        return $this->isDebug
            ? ""
            : sprintf(
                ' integrity="sha256-%s"',
                $this->registry->get($assetPath)->getDigest()
            );
    }
}
