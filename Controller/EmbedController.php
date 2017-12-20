<?php

namespace Becklyn\AssetsBundle\Controller;

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
     * @var bool
     */
    private $isDebug;


    /**
     * @param FileLoader $loader
     * @param bool       $isDebug
     */
    public function __construct (FileLoader $loader, ExtensionMimeTypeGuesser $mimeTypeGuesser, bool $isDebug)
    {
        $this->isDebug = $isDebug;
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->loader = $loader;
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
            $fileContent = $this->getFileHeaderHeader($assetPath, $filePath) . $this->loader->loadFile($assetPath);

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


    /**
     * Returns the file header for the specific file
     *
     * @param string $assetPath
     * @param string $filePath
     * @return string
     */
    private function getFileHeaderHeader (string $assetPath, string $filePath) : string
    {
        switch (\pathinfo($assetPath, \PATHINFO_EXTENSION))
        {
            case "css":
            case "js":
                return <<<HEADER
/*
 * Embed file
 *    {$assetPath}
 * from
 *    {$filePath}
 */

HEADER;

            default:
                return "";
        }
    }
}
