<?php

namespace Becklyn\AssetsBundle\Controller;

use Becklyn\AssetsBundle\Embed\EmbedFileHeader;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\File\ExtensionMimeTypeGuesser;
use Becklyn\AssetsBundle\Loader\FileLoader;
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
     * @var ExtensionMimeTypeGuesser
     */
    private $mimeTypeGuesser;


    /**
     * @var EmbedFileHeader
     */
    private $embedFileHeader;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @param FileLoader               $loader
     * @param ExtensionMimeTypeGuesser $mimeTypeGuesser
     * @param EmbedFileHeader          $embedFileHeader
     * @param bool                     $isDebug
     */
    public function __construct (FileLoader $loader, ExtensionMimeTypeGuesser $mimeTypeGuesser, EmbedFileHeader $embedFileHeader, bool $isDebug)
    {
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->loader = $loader;
        $this->embedFileHeader = $embedFileHeader;
        $this->isDebug = $isDebug;
    }


    /**
     * @param string $path
     * @return BinaryFileResponse
     */
    public function embed (string $path) : Response
    {
        if (!$this->isDebug)
        {
            throw new NotFoundHttpException("Assets embedding disabled in prod.");
        }

        try
        {
            $assetPath = \rawurldecode($path);
            $filePath = $this->loader->getFilePath($assetPath);
            $fileContent = $this->embedFileHeader->getFileHeader($assetPath, $filePath) . $this->loader->loadFile($assetPath);

            $headers = [
                "Content-Type" => "{$this->mimeTypeGuesser->guess($filePath)};charset=utf-8",
            ];

            return new Response($fileContent, 200, $headers);
        }
        catch (AssetsException $e)
        {
            throw new NotFoundHttpException("Asset not found.", $e);
        }
    }
}
