<?php

namespace Becklyn\AssetsBundle\Controller;

use Becklyn\AssetsBundle\Asset\NamespacedAsset;
use Becklyn\AssetsBundle\Entry\EntryNamespaces;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\File\ExtensionMimeTypeGuesser;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
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
     * @var bool
     */
    private $isDebug;


    /**
     * @param EntryNamespaces          $entryNamespaces
     * @param ExtensionMimeTypeGuesser $mimeTypeGuesser
     * @param bool                     $isDebug
     */
    public function __construct (EntryNamespaces $entryNamespaces, ExtensionMimeTypeGuesser $mimeTypeGuesser, bool $isDebug)
    {
        $this->entryNamespaces = $entryNamespaces;
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->isDebug = $isDebug;
    }


    /**
     * @param string $path
     * @return BinaryFileResponse
     */
    public function embed (string $path) : BinaryFileResponse
    {
        if (!$this->isDebug)
        {
            throw new NotFoundHttpException("Assets embedding disabled in prod.");
        }

        try
        {
            $asset = NamespacedAsset::createFromFullPath(\rawurldecode($path));
            $filePath = $this->entryNamespaces->getFilePath($asset);
            return new BinaryFileResponse(
                $filePath,
                200,
                [
                    "Content-Type" => "{$this->mimeTypeGuesser->guess($filePath)};charset=utf-8",
                ]
            );
        }
        catch (AssetsException $e)
        {
            throw new NotFoundHttpException("Asset not found.");
        }
    }
}
