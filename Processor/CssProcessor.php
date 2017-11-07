<?php

namespace Becklyn\AssetsBundle\Processor;

use Becklyn\AssetsBundle\Asset\AssetsCache;


/**
 *
 */
class CssProcessor implements AssetProcessor
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
     * @inheritdoc
     */
    public function process (string $assetPath, string $fileContent) : string
    {
        return \preg_replace_callback(
            '~url\\(\s*(?<path>.*?)\s*\\)~i',
            function (array $matches) use ($assetPath)
            {
                return $this->replaceUrl($assetPath, $matches);
            },
            $fileContent
        );
    }


    /**
     * Replaces the URL with the replaced one
     *
     * @param array $matches
     * @return mixed|string
     */
    private function replaceUrl (string $assetPath, array $matches) : string
    {
        $path = $matches["path"];
        $first = substr($matches["path"], 0, 1);
        $last = \substr($matches["path"], -1);
        $quotes = "";

        // check if quoted
        if ($first === "'" || $first === '"')
        {
            if ($first !== $last)
            {
                // looks like invalid CSS, as there is a leading quote, but no closing one, so bail
                return $matches[0];
            }

            $quotes = $first;
            $path = \substr($path, 1, -1);
        }

        $resolvedPath = $this->resolvePath($assetPath, $path);
        $asset = $this->cache->get($resolvedPath);

        if (null !== $asset)
        {
            // if an asset was found, overwrite the basename of the path with the cached asset
            $path = dirname($path) . "/{$asset->getOutputFileName()}";
        }

        return "url({$quotes}{$path}{$quotes})";
    }


    /**
     * Resolves the path to the given asset
     *
     * @param string $file
     * @param string $import
     * @return string
     */
    private function resolvePath (string $file, string $import)
    {
        if ("/" === $import[0])
        {
            return $import;
        }

        $segments = explode("/", rtrim($import, "/"));
        $dir = dirname($file);

        while ("/" !== $dir && !empty($segments) && (".." === $segments[0] || "." === $segments[0]))
        {
            $segment = \array_shift($segments);

            if (".." === $segment)
            {
                $dir = dirname($dir);
            }
        }

        // if the import has to many levels up
        if ("/" === $dir || empty($segments))
        {
            return $import;
        }

        return $dir . "/" . implode("/", $segments);

    }
}
