<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Mime\MimeTypes;

class AssetMimeTypeGuesser
{
    private static array $mimeTypes = [
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

        // fonts
        'woff' => 'application/font-woff',
        'woff2' => 'application/font-woff2',
        'ttf' => 'application/x-font-truetype',
        'otf' => 'application/x-font-opentype',
        'eot' => 'application/vnd.ms-fontobject',

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


    private MimeTypes $coreGuesser;
    private NamespaceRegistry $namespaceRegistry;


    public function __construct (NamespaceRegistry $namespaceRegistry)
    {
        $this->coreGuesser = MimeTypes::getDefault();
        $this->namespaceRegistry = $namespaceRegistry;
    }


    /**
     * Guesses the mime type.
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

            $filePath = $this->namespaceRegistry->getFilePath($asset);
            return $this->coreGuesser->guessMimeType($filePath);
        }
        catch (AssetsException | Exception $e)
        {
            return "application/octet-stream";
        }
    }
}
