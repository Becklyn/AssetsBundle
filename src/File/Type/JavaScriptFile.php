<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;

class JavaScriptFile extends FileType
{
    use GenericFileHeaderTrait;


    /**
     * @inheritDoc
     */
    public function processForDev (Asset $asset, string $filePath, string $fileContent) : string
    {
        $header = $this->generateGenericFileDebugInfo($asset, $filePath, '/*', '*/');
        return $header . $fileContent;
    }


    /**
     * @inheritDoc
     */
    public function getHtmlLinkFormat () : ?string
    {
        return '<script defer src="%s"%s%s></script>';
    }


    /**
     * @inheritDoc
     */
    public function shouldIncludeHashInFileName () : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function shouldBeGzipCompressed () : bool
    {
        return true;
    }
}
