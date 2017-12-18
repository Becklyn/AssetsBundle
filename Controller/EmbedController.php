<?php

namespace Becklyn\AssetsBundle\Controller;

use Becklyn\AssetsBundle\Asset\NamespacedAsset;
use Becklyn\AssetsBundle\Entry\EntryNamespaces;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\File\ExtensionMimeTypeGuesser;
use Becklyn\AssetsBundle\Processor\ProcessorRegistry;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class EmbedController
{
    /**
     * @var EntryNamespaces
     */
    private $entryNamespaces;


    /**
     * @var ExtensionMimeTypeGuesser
     */
    private $mimeTypeGuesser;


    /**
     * @var ProcessorRegistry
     */
    private $processorRegistry;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @param EntryNamespaces          $entryNamespaces
     * @param ExtensionMimeTypeGuesser $mimeTypeGuesser
     * @param bool                     $isDebug
     */
    public function __construct (EntryNamespaces $entryNamespaces, ExtensionMimeTypeGuesser $mimeTypeGuesser, ProcessorRegistry $processorRegistry, bool $isDebug)
    {
        $this->entryNamespaces = $entryNamespaces;
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->processorRegistry = $processorRegistry;
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
            $asset = NamespacedAsset::createFromFullPath($assetPath);
            $filePath = $this->entryNamespaces->getFilePath($asset);
            $processor = $this->processorRegistry->get($filePath);

            $headers = [
                "Content-Type" => "{$this->mimeTypeGuesser->guess($filePath)};charset=utf-8",
            ];

            if (!\is_file($filePath))
            {
                throw new NotFoundHttpException(sprintf(
                    "Asset not found at '%s'.",
                    $filePath
                ));
            }

            $fileContent = $this->getFileHeaderHeader($assetPath, $filePath) . \file_get_contents($filePath);

            if (null !== $processor )
            {
                $fileContent = $processor->process($assetPath, $fileContent);

                return new Response($fileContent, 200, $headers);
            }

            return new BinaryFileResponse($filePath, 200, $headers);
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
