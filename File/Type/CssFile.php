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
    public function processForDev (Asset $asset, string $filePath, string $fileContent) : string
    {
        // only rewrite namespaced imports in dev + add file header
        $fileHeader = $this->generateGenericFileHeader($asset, $filePath, '/*', '*/');
        $fileContent = $this->importRewriter->rewriteNamespacedImports($fileContent);

        return $fileHeader . $fileContent;
    }


    /**
     * @inheritDoc
     */
    public function processForProd (Asset $asset, string $fileContent) : string
    {
        // rewrite namespaced + relative imports in prod
        $fileContent = $this->importRewriter->rewriteNamespacedImports($fileContent);
        return $this->importRewriter->rewriteRelativeImports($asset, $fileContent);
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
}
