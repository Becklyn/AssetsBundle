<?php

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;


class JavaScriptFile extends FileType
{
    use GenericFileHeaderTrait;


    /**
     * @inheritDoc
     */
    public function addFileHeader (Asset $asset, string $filePath, string $fileContent) : string
    {
        $header = $this->generateGenericFileHeader($asset, $filePath, '/*', '*/');
        return $header . $fileContent;
    }
}
