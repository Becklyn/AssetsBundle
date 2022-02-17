<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Html;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Data\AssetEmbed;
use Becklyn\AssetsBundle\Dependency\DependencyMap;
use Becklyn\AssetsBundle\Dependency\DependencyMapFactory;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Exception\NotEmbeddableFileTypeException;
use Becklyn\AssetsBundle\File\FileTypeRegistry;
use Becklyn\AssetsBundle\Url\AssetUrl;
use Becklyn\HtmlBuilder\Builder\HtmlBuilder;

class AssetHtmlGenerator
{
    private AssetsRegistry $registry;
    private AssetUrl $assetUrl;
    private FileTypeRegistry $fileTypeRegistry;
    private bool $isDebug;
    private DependencyMap $dependencyMap;
    private HtmlBuilder $htmlBuilder;
    private bool $allowCors;


    public function __construct (
        AssetsRegistry $registry,
        AssetUrl $assetUrl,
        FileTypeRegistry $fileTypeRegistry,
        bool $isDebug,
        DependencyMapFactory $dependencyMapFactory,
        bool $allowCors = false
    )
    {
        $this->registry = $registry;
        $this->assetUrl = $assetUrl;
        $this->fileTypeRegistry = $fileTypeRegistry;
        $this->isDebug = $isDebug;
        $this->dependencyMap = $dependencyMapFactory->getDependencyMap();
        $this->htmlBuilder = new HtmlBuilder();
        $this->allowCors = $allowCors;
    }


    public function getRegistry () : AssetsRegistry
    {
        return $this->registry;
    }


    /**
     * @param string[] $assetPaths
     *
     * @throws AssetsException
     */
    public function linkAssets (array $assetPaths, bool $withDependencies = true) : string
    {
        $html = "";

        $assetEmbeds = $withDependencies
            ? $this->dependencyMap->getImportsWithDependencies($assetPaths)
            : \array_map(function (string $path) { return new AssetEmbed($path); }, $assetPaths);

        foreach ($assetEmbeds as $embed)
        {
            // allow URLs with integrated optional integrity.
            // just pass it behind a hash:
            // https://example.org/test.js#sha256hash
            if ($embed->isExternal())
            {
                $fragment = \parse_url($embed->getAssetPath(), \PHP_URL_FRAGMENT);
                $extension = \pathinfo(\parse_url($embed->getAssetPath(), \PHP_URL_PATH), \PATHINFO_EXTENSION);
                $embed->setUrl($embed->getAssetPath());

                if (null !== $fragment)
                {
                    $embed->setUrl(\str_replace("#{$fragment}", "", $embed->getAssetPath()));
                    \parse_str($fragment, $urlParameters);

                    foreach (["integrity", "crossorigin"] as $param)
                    {
                        $value = \trim($urlParameters[$param] ?? "");

                        if ("" !== $value)
                        {
                            $embed->setAttribute($param, $value);
                        }
                    }

                    if (isset($urlParameters["type"]))
                    {
                        $extension = $urlParameters["type"];
                    }
                }

                $fileType = $this->fileTypeRegistry->getByFileExtension($extension);
            }
            else
            {
                $asset = Asset::createFromAssetPath($embed->getAssetPath());
                $embed->setUrl($this->assetUrl->generateUrl($asset));
                $fileType = $this->fileTypeRegistry->getFileType($asset);
                $extension = $asset->getFileType();

                if (!$this->isDebug)
                {
                    $hash = $this->registry->get($asset)->getHash();

                    $integrityHash = null !== $hash
                        ? \sprintf("%s-%s", Asset::HASH_ALGORITHM, $hash)
                        : "";

                    $embed->setAttribute("integrity", $integrityHash);
                }
            }

            if ($this->allowCors)
            {
                $embed->setAttribute("crossorigin", "anonymous");
            }

            try
            {
                $html .= $this->htmlBuilder->buildElement($fileType->buildElementForEmbed($embed));
            }
            catch (NotEmbeddableFileTypeException $e)
            {
                throw new AssetsException(\sprintf(
                    "No HTML link format found for file of type: %s",
                    $extension
                ), $e);
            }
        }

        return $html;
    }
}
