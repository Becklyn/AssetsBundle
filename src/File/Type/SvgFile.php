<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;

class SvgFile extends SpecializedFileType
{
    use GenericFileHeaderTrait;


    /**
     * @inheritDoc
     */
    public function processForDev (Asset $asset, string $filePath, string $fileContent) : string
    {
        // the comment must be at the bottom, because if the SVG has a <?xml .. tag, it needs to be the
        // very first thing in the file and this debug info would be above it.
        $footer = $this->generateGenericFileDebugInfo($asset, $filePath, '<!--', '-->');
        return $fileContent . $footer;
    }


    /**
     * @inheritDoc
     */
    public function shouldBeGzipCompressed () : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public static function supportsExtension () : string
    {
        return "svg";
    }
}
