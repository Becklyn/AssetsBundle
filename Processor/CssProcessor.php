<?php

namespace Becklyn\AssetsBundle\Processor;

use Becklyn\AssetsBundle\Asset\AssetsCache;
use Symfony\Component\Routing\RouterInterface;


/**
 *
 */
class CssProcessor implements AssetProcessorInterface
{
    /**
     * @var AssetsCache
     */
    private $cache;


    /**
     * @var RouterInterface
     */
    private $router;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @param AssetsCache     $cache
     * @param RouterInterface $router
     * @param bool            $isDebug
     */
    public function __construct (AssetsCache $cache, RouterInterface $router, bool $isDebug)
    {
        $this->cache = $cache;
        $this->router = $router;
        $this->isDebug = $isDebug;
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

        $path = $this->rewritePath($assetPath, $path);
        return "url({$quotes}{$path}{$quotes})";
    }


    /**
     * Rewrites the path to fix the local path to an update one
     *
     * @param string $assetPath
     * @param string $path
     * @return string
     */
    private function rewritePath (string $assetPath, string $path) : string
    {
        // pass URLs untouched
        if (1 === \preg_match('~^(//|https?://)~', $path))
        {
            return $path;
        }

        $resolvedPath = $this->resolvePath($assetPath, $path);
        if ($this->isDebug)
        {
            return $this->router->generate("becklyn_assets_embed", [
                "path" => \rawurlencode($resolvedPath),
            ]);
        }
        else
        {
            $asset = $this->cache->get($resolvedPath);

            if (null !== $asset)
            {
                // if an asset was found, overwrite the basename of the path with the cached asset
                return dirname($path) . "/{$asset->getOutputFileName()}";
            }
        }

        return $path;
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

        // if the import has too many levels up
        if ("/" === $dir || empty($segments))
        {
            return $import;
        }

        return $dir . "/" . implode("/", $segments);
    }
}
