<?php

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsCache;


class CssFile extends FileType
{
    use GenericFileHeaderTrait;


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
     * @inheritDoc
     */
    public function prependFileHeader (Asset $asset, string $filePath, string $fileContent) : string
    {
        $header = $this->generateGenericFileHeader($asset, $filePath, '/*', '*/');
        return $header . $fileContent;
    }


    /**
     * @inheritDoc
     */
    public function importDeferred () : bool
    {
        // must be loaded deferred, as it might have dependencies on other files
        return true;
    }


    /**
     * @inheritDoc
     */
    public function getHtmlLinkFormat () : ?string
    {
        return '<link rel="stylesheet" href="%s"%s>';
    }


    /**
     * @inheritDoc
     */
    public function processForProd (Asset $asset, string $fileContent) : string
    {
        return \preg_replace_callback(
            '~url\\(\s*(?<path>.*?)\s*\\)~i',
            function (array $matches) use ($asset)
            {
                return $this->replaceUrl($asset, $matches);
            },
            $fileContent
        );
    }


    /**
     * Replaces the URL with the replaced one
     *
     * @param Asset $asset
     * @param array $matches
     * @return string
     */
    private function replaceUrl (Asset $asset, array $matches) : string
    {
        $path = $matches["path"];
        $openingQuote = substr($matches["path"], 0, 1);
        $closingQuote = \substr($matches["path"], -1);
        $usedQuotes = "";

        // check if quoted and whether valid quoted
        if ($openingQuote === "'" || $openingQuote === '"')
        {
            if ($openingQuote !== $closingQuote)
            {
                // looks like invalid CSS, as there is a leading quote, but no closing one, so bail
                return $matches[0];
            }

            // strip quotes from path
            $path = \substr($path, 1, -1);
            $usedQuotes = $openingQuote;
        }

        $path = $this->rewritePath($asset, $path);
        return "url({$usedQuotes}{$path}{$usedQuotes})";
    }


    /**
     * Rewrites the path to fix the local path to an update one
     *
     * @param string $assetPath
     * @param string $path
     * @return string
     */
    private function rewritePath (Asset $asset, string $path) : string
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
            // if an asset was found, overwrite the basename of the path with the cached asset
            return dirname($path) . "/{$assetAtPath->getDumpFilePath()}";
        }

        return $path;
    }
}
