<?php

namespace Becklyn\AssetsBundle\File\Type\Css;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;


class CssImportRewriter
{
    /**
     * @var AssetsCache
     */
    private $cache;


    /**
     * @param AssetsCache $cache
     */
    public function __construct (AssetsCache $cache)
    {
        $this->cache = $cache;
    }


    /**
     * Rewrites all imports to use the names from the cache
     *
     * @param Asset  $asset
     * @param string $fileContent
     * @return string
     */
    public function rewriteStaticImports (Asset $asset, string $fileContent) : string
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
     * Rewrites the path to fix the local path to an update one
     *
     * @param string $assetPath
     * @param string $path
     * @return string
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
