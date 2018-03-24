<?php

namespace Becklyn\AssetsBundle\Controller;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\File\AssetMimeTypeGuesser;
use Becklyn\AssetsBundle\File\FileLoader;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class EmbedController
{
    /**
     * @var FileLoader
     */
    private $loader;


    /**
     * @var AssetMimeTypeGuesser
     */
    private $mimeTypeGuesser;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @param FileLoader           $loader
     * @param AssetMimeTypeGuesser $mimeTypeGuesser
     * @param bool                 $isDebug
     */
    public function __construct (FileLoader $loader, AssetMimeTypeGuesser $mimeTypeGuesser, bool $isDebug)
    {
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->loader = $loader;
        $this->isDebug = $isDebug;
    }


    /**
     * @param string $path
     * @return BinaryFileResponse
     */
    public function embed (string $namespace, string $path) : Response
    {
        if (!$this->isDebug)
        {
            throw new NotFoundHttpException("Assets embedding disabled in prod.");
        }

        try
        {
            $asset = new Asset($namespace, $path);
            $fileContent = $this->loader->loadFile($asset, FileLoader::MODE_DEV);

            $headers = [
                "Content-Type" => "{$this->mimeTypeGuesser->guess($asset)};charset=utf-8",
            ];

            return new Response($fileContent, 200, $headers);
        }
        catch (AssetsException $e)
        {
            throw new NotFoundHttpException("Asset not found.", $e);
        }
    }
}
