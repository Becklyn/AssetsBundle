<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Data\AssetEmbed;
use Becklyn\AssetsBundle\File\Type\Css\CssImportRewriter;
use Becklyn\HtmlBuilder\Node\HtmlElement;

class CssFile extends SpecializedFileType
{
    use GenericFileHeaderTrait;

    /**
     * @var CssImportRewriter
     */
    private $importRewriter;


    /**
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
        $fileHeader = $this->generateGenericFileDebugInfo($asset, $filePath, '/*', '*/');
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
    public function buildElementForEmbed (AssetEmbed $embed) : HtmlElement
    {
        return new HtmlElement(
            "link",
            $embed->getAttributes()
                ->set("rel", "stylesheet")
                ->set("href", $embed->getUrl())
        );
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
        return "css";
    }
}
