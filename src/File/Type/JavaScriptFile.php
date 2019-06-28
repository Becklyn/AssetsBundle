<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Data\AssetEmbed;
use Becklyn\HtmlBuilder\Node\HtmlElement;

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
    public function buildElementForEmbed (AssetEmbed $embed) : HtmlElement
    {
        return new HtmlElement(
            "script",
            $embed->getAttributes()
                ->set("defer", true)
                ->set("src", $embed->getUrl())
        );
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
