<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Html;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Dependency\DependencyMap;
use Becklyn\AssetsBundle\Dependency\DependencyMapFactory;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\File\FileTypeRegistry;
use Becklyn\AssetsBundle\Url\AssetUrl;

class AssetHtmlGenerator
{
    /**
     * @var AssetsRegistry
     */
    private $registry;


    /**
     * @var AssetUrl
     */
    private $assetUrl;


    /**
     * @var FileTypeRegistry
     */
    private $fileTypeRegistry;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @var DependencyMap
     */
    private $dependencyMap;


    /**
     * @param AssetsRegistry       $registry
     * @param AssetUrl             $assetUrl
     * @param FileTypeRegistry     $fileTypeRegistry
     * @param bool                 $isDebug
     * @param DependencyMapFactory $dependencyMapFactory
     */
    public function __construct (
        AssetsRegistry $registry,
        AssetUrl $assetUrl,
        FileTypeRegistry $fileTypeRegistry,
        bool $isDebug,
        DependencyMapFactory $dependencyMapFactory
    )
    {
        $this->registry = $registry;
        $this->assetUrl = $assetUrl;
        $this->fileTypeRegistry = $fileTypeRegistry;
        $this->isDebug = $isDebug;
        $this->dependencyMap = $dependencyMapFactory->getDependencyMap();
    }


    /**
     * @param string[] $assetPaths
     *
     * @throws AssetsException
     */
    public function linkAssets (array $assetPaths, bool $withDependencies = true) : string
    {
        $html = "";

        if ($withDependencies)
        {
            $assetPaths = $this->dependencyMap->getImportsWithDependencies($assetPaths);
        }

        foreach ($assetPaths as $assetPath)
        {
            // allow URLs with integrated optional integrity.
            // just pass it behind a hash:
            // https://example.org/test.js#sha256hash
            if (1 === \preg_match('~^(https?:)?//~', $assetPath))
            {
                $fragment = \parse_url($assetPath, \PHP_URL_FRAGMENT);
                $fileExtension = \pathinfo(\parse_url($assetPath, \PHP_URL_PATH), \PATHINFO_EXTENSION);
                $assetUrl = $assetPath;
                $integrity = "";
                $crossOrigin = "";

                if (null !== $fragment)
                {
                    $assetUrl = \str_replace("#{$fragment}", "", $assetPath);
                    \parse_str($fragment, $urlParameters);

                    if (isset($urlParameters["integrity"]) && "" !== $urlParameters["integrity"])
                    {
                        $integrity = \sprintf(' integrity="%s"', $urlParameters["integrity"]);
                    }

                    if (isset($urlParameters["crossorigin"]) && "" !== $urlParameters["crossorigin"])
                    {
                        $crossOrigin = \sprintf(' crossorigin="%s"', $urlParameters["crossorigin"]);
                    }

                    $extension = $urlParameters["type"] ?? $fileExtension;
                    $fileType = $this->fileTypeRegistry->getByFileExtension($extension);
                }
                else
                {
                    $fileType = $this->fileTypeRegistry->getByFileExtension($fileExtension);
                }
            }
            else
            {
                $asset = Asset::createFromAssetPath($assetPath);
                $assetUrl = $this->assetUrl->generateUrl($asset);
                $fileType = $this->fileTypeRegistry->getFileType($asset);
                $extension = $asset->getFileType();
                $integrity = $this->isDebug ? "" : $this->getIntegrityHtml($asset);
                // Internal URLs don't need any `crossorigin` configuration
                $crossOrigin = "";
            }

            $htmlLinkFormat = $fileType->getHtmlLinkFormat();

            if (null === $htmlLinkFormat)
            {
                throw new AssetsException(\sprintf(
                    "No HTML link format found for file of type: %s",
                    $extension
                ));
            }

            $html .= \sprintf($htmlLinkFormat, $assetUrl, $integrity, $crossOrigin);
        }

        return $html;
    }


    /**
     * Returns the integrity HTML snippet.
     *
     * @param Asset $asset
     *
     * @throws AssetsException
     *
     * @return string
     */
    private function getIntegrityHtml (Asset $asset) : string
    {
        return $this->isDebug
            ? ""
            : \sprintf(
                ' integrity="sha256-%s"',
                $this->registry->get($asset)->getHash()
            );
    }
}
