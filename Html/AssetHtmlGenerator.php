<?php

namespace Becklyn\AssetsBundle\Html;

use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Url\AssetUrl;
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
     * @var AssetUrl
     */
    private $assetUrl;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     *
     * @param AssetsRegistry $registry
     * @param AssetUrl       $assetUrl
     * @param bool           $isDebug
     */
    public function __construct (AssetsRegistry $registry, AssetUrl $assetUrl, bool $isDebug)
    {
        $this->registry = $registry;
        $this->assetUrl = $assetUrl;
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
                return $this->link($assetPaths, '<script defer src="%s"%s></script>');

            case self::TYPE_CSS:
                return $this->link($assetPaths, '<link rel="stylesheet" href="%s"%s>');

            default:
                throw new AssetsException(sprintf(
                    "Invalid asset type: %s",
                    $type
                ));
        }
    }


    /**
     * Links the given assets with the given HTML snippet.
     *
     * @param array  $assetPaths
     * @param string $htmlSnippet   the template snippet. Needs to contain two placeholders.
     * @return string
     * @throws AssetsException
     */
    private function link (array $assetPaths, string $htmlSnippet)
    {
        $html = "";

        foreach ($assetPaths as $assetPath)
        {
            $html .= sprintf(
                $htmlSnippet,
                $this->assetUrl->generateUrl($assetPath),
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
