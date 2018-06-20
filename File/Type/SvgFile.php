<?php

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;


class SvgFile extends FileType
{
    use GenericFileHeaderTrait;


    /**
     * @inheritDoc
     */
    public function processForDev (Asset $asset, string $filePath, string $fileContent) : string
    {
        $header = $this->generateGenericFileHeader($asset, $filePath, '<!--', '-->');
        return $header . $fileContent;
    }
}
