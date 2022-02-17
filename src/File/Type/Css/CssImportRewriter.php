<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File\Type\Css;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Url\AssetUrl;

class CssImportRewriter
{
    private AssetsCache $cache;
    private AssetUrl $assetUrl;


    public function __construct (AssetsCache $cache, AssetUrl $assetUrl)
    {
        $this->cache = $cache;
        $this->assetUrl = $assetUrl;
    }


    /**
     * Rewrites the path to namespaced assets.
     */
    public function rewriteNamespacedImports (string $fileContent) : string
    {
        $importParser = new CssUrlImportParser();
        return $importParser->replaceValidImports(
            $fileContent,
            function (string $path)
            {
                try
                {
                    $asset = Asset::createFromAssetPath($path);
                    return $this->assetUrl->generateUrl($asset);
                }
                catch (AssetsException $e)
                {
                    // wasn't an asset path
                    return $path;
                }
            }
        );
    }


    /**
     * Rewrites all imports to use the names from the cache.
     */
    public function rewriteRelativeImports (Asset $asset, string $fileContent) : string
    {
        $importParser = new CssUrlImportParser();
        return $importParser->replaceValidImports(
            $fileContent,
            function (string $path) use ($asset)
            {
                return $this->rewritePathInStaticImport($asset, $path);
            }
        );
    }


    /**
     * Rewrites the path to fix the local path to an update one.
     */
    private function rewritePathInStaticImport (Asset $asset, string $path) : string
    {
        // pass all URLs untouched, either "//..." or "schema:", where schema is a typical schema, that includes
        // http: https: and data:
        if (1 === \preg_match('~^(//|[a-z]+:)~', $path))
        {
            return $path;
        }

        $assetAtPath = $asset->getRelativeAsset($path);
        $assetAtPath = $this->cache->get($assetAtPath);

        if (null !== $assetAtPath)
        {
            // only overwrite the file name of the import
            $baseName = \basename($assetAtPath->getDumpFilePath());
            return \preg_replace('~(?<=^|/)[^/]*?$~', $baseName, $path);
        }

        return $path;
    }
}
