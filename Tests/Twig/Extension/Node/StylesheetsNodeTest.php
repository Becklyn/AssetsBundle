<?php

namespace Becklyn\AssetsBundle\tests\Twig\Node;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Data\CachedReference;
use Becklyn\AssetsBundle\Path\PathGenerator;
use Becklyn\AssetsBundle\tests\BaseTest;
use Becklyn\AssetsBundle\Twig\Extension\AssetsTwigExtension;
use Becklyn\AssetsBundle\Twig\Extension\Node\StylesheetsNode;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Twig_Compiler;
use Twig_Environment;
use Twig_Loader_Array;
use Twig_Node_Expression_Binary_Concat;
use Twig_Node_Expression_Constant;


class StylesheetsNodeTest extends BaseTest
{
    /**
     * @var PathGenerator|MockObject
     */
    private $pathGenerator;


    /**
     * @var Twig_Compiler|MockObject
     */
    private $compiler;


    public function setUp ()
    {
        $this->pathGenerator = self::getMockBuilder(PathGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig = new Twig_Environment(new Twig_Loader_Array([]), [
            "cache" => false,
        ]);

        $twig->addExtension(new AssetsTwigExtension($this->pathGenerator));

        $this->compiler = new Twig_Compiler($twig);
    }


    /**
     * @group node
     * @group stylesheet
     * @group twig
     */
    public function testCompileSingleAssetWithoutSubresourceIntegrity ()
    {
        $assetReference = new AssetReference("a/b/a.css", AssetReference::TYPE_STYLESHEET);

        $this->pathGenerator
            ->expects($this->once())
            ->method("getDisplayAssetReference")
            ->with($assetReference)
            ->willReturn($assetReference);

        $files = [
            new Twig_Node_Expression_Constant("a/b/a.css", [42]),
        ];

        $node = new StylesheetsNode($this->pathGenerator, $files, 42, "stylesheets");
        $node->compile($this->compiler);

        $source = $this->compiler->getSource();
        $expectedSource = <<<RAW
// line 42
echo '<link rel="stylesheet" href="' . \$this->env->getExtension("asset")->getAssetUrl("a/b/a.css") . '">';\n
RAW;

        self::assertSame($expectedSource, $source);
    }


    /**
     * @group node
     * @group stylesheet
     * @group twig
     */
    public function testCompileSingleAssetConcatWithoutSubresourceIntegrity ()
    {
        $assetReference = new AssetReference("a/b/cd.css", AssetReference::TYPE_STYLESHEET);

        $this->pathGenerator
            ->expects($this->once())
            ->method("getDisplayAssetReference")
            ->with($assetReference)
            ->willReturn($assetReference);

        $files = [
            new Twig_Node_Expression_Binary_Concat(
                new Twig_Node_Expression_Constant("a/b/", [42]),
                new Twig_Node_Expression_Constant("cd.css", [42]),
                42
            ),
        ];

        $node = new StylesheetsNode($this->pathGenerator, $files, 42, "javascripts");
        $node->compile($this->compiler);

        $source = $this->compiler->getSource();
        $expectedSource = <<<RAW
// line 42
echo '<link rel="stylesheet" href="' . \$this->env->getExtension("asset")->getAssetUrl("a/b/cd.css") . '">';\n
RAW;

        self::assertSame($expectedSource, $source);
    }


    /**
     * @group node
     * @group stylesheet
     * @group twig
     */
    public function testCompileMultipleAssetsWithoutSubresourceIntegrity ()
    {
        $assetReferenceA = new AssetReference("a/b/a.css", AssetReference::TYPE_STYLESHEET);
        $assetReferenceB = new AssetReference("b/c/b.css", AssetReference::TYPE_STYLESHEET);

        $this->pathGenerator
            ->expects($this->exactly(2))
            ->method("getDisplayAssetReference")
            ->withConsecutive(
                [$assetReferenceA],
                [$assetReferenceB]
            )
            ->willReturnOnConsecutiveCalls(
                $assetReferenceA,
                $assetReferenceB
            );

        $files = [
            new Twig_Node_Expression_Constant("a/b/a.css", [42]),
            new Twig_Node_Expression_Constant("b/c/b.css", [42]),
        ];

        $node = new StylesheetsNode($this->pathGenerator, $files, 42, "stylesheets");
        $node->compile($this->compiler);

        $source = $this->compiler->getSource();
        $expectedSource = <<<RAW
// line 42
echo '<link rel="stylesheet" href="' . \$this->env->getExtension("asset")->getAssetUrl("a/b/a.css") . '">';
echo '<link rel="stylesheet" href="' . \$this->env->getExtension("asset")->getAssetUrl("b/c/b.css") . '">';\n
RAW;

        self::assertSame($expectedSource, $source);
    }


    /**
     * @group node
     * @group stylesheet
     * @group twig
     */
    public function testCompileSingleAssetWithSubresourceIntegrity ()
    {
        $assetReference = new AssetReference("a/b/a.css", AssetReference::TYPE_STYLESHEET);
        $cachedReference = new CachedReference("a/b/a.css", "hash", "sha1337");

        $this->pathGenerator
            ->expects($this->once())
            ->method("getDisplayAssetReference")
            ->with($assetReference)
            ->willReturn($cachedReference);

        $files = [
            new Twig_Node_Expression_Constant("a/b/a.css", [42]),
        ];

        $node = new StylesheetsNode($this->pathGenerator, $files, 42, "stylesheets");
        $node->compile($this->compiler);

        $source = $this->compiler->getSource();
        $expectedSource = <<<RAW
// line 42
echo '<link rel="stylesheet" href="' . \$this->env->getExtension("asset")->getAssetUrl("a/b/a.css") . '" integrity="sha1337-hash">';\n
RAW;

        self::assertSame($expectedSource, $source);
    }


    /**
     * @group node
     * @group stylesheet
     * @group twig
     */
    public function testCompileSingleAssetConcatWithSubresourceIntegrity ()
    {
        $assetReference = new AssetReference("a/b/cd.css", AssetReference::TYPE_STYLESHEET);
        $cachedReference = new CachedReference("a/b/cd.css", "hash", "sha1337");

        $this->pathGenerator
            ->expects($this->once())
            ->method("getDisplayAssetReference")
            ->with($assetReference)
            ->willReturn($cachedReference);

        $files = [
            new Twig_Node_Expression_Binary_Concat(
                new Twig_Node_Expression_Constant("a/b/", [42]),
                new Twig_Node_Expression_Constant("cd.css", [42]),
                42
            ),
        ];

        $node = new StylesheetsNode($this->pathGenerator, $files, 42, "stylesheets");
        $node->compile($this->compiler);

        $source = $this->compiler->getSource();
        $expectedSource = <<<RAW
// line 42
echo '<link rel="stylesheet" href="' . \$this->env->getExtension("asset")->getAssetUrl("a/b/cd.css") . '" integrity="sha1337-hash">';\n
RAW;

        self::assertSame($expectedSource, $source);
    }


    /**
     * @group node
     * @group stylesheet
     * @group twig
     */
    public function testCompileMultipleAssetsWithSubresourceIntegrity ()
    {
        $assetReferenceA = new AssetReference("a/b/a.css", AssetReference::TYPE_STYLESHEET);
        $assetReferenceB = new AssetReference("b/c/b.css", AssetReference::TYPE_STYLESHEET);

        $cachedReferenceA = new CachedReference("a/b/a.css", "hashA", "sha1337");
        $cachedReferenceB = new CachedReference("b/c/b.css", "hashB", "sha42");

        $this->pathGenerator
            ->expects($this->exactly(2))
            ->method("getDisplayAssetReference")
            ->withConsecutive(
                [$assetReferenceA],
                [$assetReferenceB]
            )
            ->willReturnOnConsecutiveCalls(
                $cachedReferenceA,
                $cachedReferenceB
            );

        $files = [
            new Twig_Node_Expression_Constant("a/b/a.css", [42]),
            new Twig_Node_Expression_Constant("b/c/b.css", [42]),
        ];

        $node = new StylesheetsNode($this->pathGenerator, $files, 42, "stylesheets");
        $node->compile($this->compiler);

        $source = $this->compiler->getSource();
        $expectedSource = <<<RAW
// line 42
echo '<link rel="stylesheet" href="' . \$this->env->getExtension("asset")->getAssetUrl("a/b/a.css") . '" integrity="sha1337-hashA">';
echo '<link rel="stylesheet" href="' . \$this->env->getExtension("asset")->getAssetUrl("b/c/b.css") . '" integrity="sha42-hashB">';\n
RAW;

        self::assertSame($expectedSource, $source);
    }
}
