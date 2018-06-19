<?php

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\File\Type\Css\CssImportRewriter;


class CssFile extends FileType
{
    use GenericFileHeaderTrait;


    /**
     * @var CssImportRewriter
     */
    private $importRewriter;


    /**
     *
     * @param CssImportRewriter $importRewriter
     */
    public function __construct (CssImportRewriter $importRewriter)
    {
        $this->importRewriter = $importRewriter;
    }


    /**
     * @inheritDoc
     */
    public function prependFileHeader (Asset $asset, string $filePath, string $fileContent) : string
    {
        $header = $this->generateGenericFileHeader($asset, $filePath, '/*', '*/');
        return $header . $fileContent;
    }


    /**
     * @inheritDoc
     */
    public function importDeferred () : bool
    {
        // must be loaded deferred, as it might have dependencies on other files
        return true;
    }


    /**
     * @inheritDoc
     */
    public function getHtmlLinkFormat () : ?string
    {
        return '<link rel="stylesheet" href="%s"%s>';
    }


    /**
     * @inheritDoc
     */
    public function processForProd (Asset $asset, string $fileContent) : string
    {
        return $this->importRewriter->rewriteStaticImports($asset, $fileContent);
    }
}
