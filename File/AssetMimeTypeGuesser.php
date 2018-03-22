<?php

namespace Becklyn\AssetsBundle\File;


use Becklyn\AssetsBundle\Asset\Asset;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;


class AssetMimeTypeGuesser
{
    /**
     * @var array
     */
    private static $mimeTypes = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',

        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    ];


    /**
     * @var MimeTypeGuesser
     */
    private $coreGuesser;


    /**
     *
     */
    public function __construct ()
    {
        $this->coreGuesser = MimeTypeGuesser::getInstance();
    }


    /**
     * Guesses the mime type.
     *
     * @param Asset $asset
     * @return string
     */
    public function guess (Asset $asset) : string
    {
        try
        {
            $predefinedType = self::$mimeTypes[$asset->getFileType()] ?? null;

            if (null !== $predefinedType)
            {
                return $predefinedType;
            }

            return $this->coreGuesser->guess($asset->getFilePath());
        }
        catch (Exception $e)
        {
            return "application/octet-stream";
        }
    }
}
