<?php

namespace Becklyn\AssetsBundle\tests\Twig\Node;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Data\CachedReference;
use Becklyn\AssetsBundle\Path\PathGenerator;
use Becklyn\AssetsBundle\tests\BaseTest;
use Becklyn\AssetsBundle\Twig\Extension\AssetsTwigExtension;
use Becklyn\AssetsBundle\Twig\Extension\Node\JavaScriptsNode;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Twig_Compiler;
use Twig_Environment;
use Twig_Loader_Array;
use Twig_Node_Expression_Binary_Concat;
use Twig_Node_Expression_Constant;


class JavaScriptsNodeTest extends BaseTest
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
     * @group javascript
     * @group twig
     */
    public function testCompileSingleAssetWithoutSubresourceIntegrity ()
    {
        $assetReference = new AssetReference("a/b/a.js", AssetReference::TYPE_JAVASCRIPT);

        $this->pathGenerator
            ->expects($this->once())
            ->method("getDisplayAssetReference")
            ->with($assetReference)
            ->willReturn($assetReference);

        $files = [
            new Twig_Node_Expression_Constant("a/b/a.js", [42]),
        ];

        $node = new JavaScriptsNode($this->pathGenerator, $files, 42, "javascripts");
        $node->compile($this->compiler);

        $source = $this->compiler->getSource();
        $expectedSource = <<<RAW
// line 42
echo '<script src="' . \$this->env->getExtension("asset")->getAssetUrl("a/b/a.js") . '"></script>';\n
RAW;

        self::assertSame($expectedSource, $source);
    }


    /**
     * @group node
     * @group javascript
     * @group twig
     */
    public function testCompileSingleAssetConcatWithoutSubresourceIntegrity ()
    {
        $assetReference = new AssetReference("a/b/cd.js", AssetReference::TYPE_JAVASCRIPT);

        $this->pathGenerator
            ->expects($this->once())
            ->method("getDisplayAssetReference")
            ->with($assetReference)
            ->willReturn($assetReference);

        $files = [
            new Twig_Node_Expression_Binary_Concat(
                new Twig_Node_Expression_Constant("a/b/", [42]),
                new Twig_Node_Expression_Constant("cd.js", [42]),
                42
            ),
        ];

        $node = new JavaScriptsNode($this->pathGenerator, $files, 42, "javascripts");
        $node->compile($this->compiler);

        $source = $this->compiler->getSource();
        $expectedSource = <<<RAW
// line 42
echo '<script src="' . \$this->env->getExtension("asset")->getAssetUrl("a/b/cd.js") . '"></script>';\n
RAW;

        self::assertSame($expectedSource, $source);
    }


    /**
     * @group node
     * @group javascript
     * @group twig
     */
    public function testCompileMultipleAssetsWithoutSubresourceIntegrity ()
    {
        $assetReferenceA = new AssetReference("a/b/a.js", AssetReference::TYPE_JAVASCRIPT);
        $assetReferenceB = new AssetReference("b/c/b.js", AssetReference::TYPE_JAVASCRIPT);

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
            new Twig_Node_Expression_Constant("a/b/a.js", [42]),
            new Twig_Node_Expression_Constant("b/c/b.js", [42]),
        ];

        $node = new JavaScriptsNode($this->pathGenerator, $files, 42, "javascripts");
        $node->compile($this->compiler);

        $source = $this->compiler->getSource();
        $expectedSource = <<<RAW
// line 42
echo '<script src="' . \$this->env->getExtension("asset")->getAssetUrl("a/b/a.js") . '"></script>';
echo '<script src="' . \$this->env->getExtension("asset")->getAssetUrl("b/c/b.js") . '"></script>';\n
RAW;

        self::assertSame($expectedSource, $source);
    }


    /**
     * @group node
     * @group javascript
     * @group twig
     */
    public function testCompileSingleAssetWithSubresourceIntegrity ()
    {
        $assetReference = new AssetReference("a/b/a.js", AssetReference::TYPE_JAVASCRIPT);
        $cachedReference = new CachedReference("a/b/a.js", "hash", "sha1337");

        $this->pathGenerator
            ->expects($this->once())
            ->method("getDisplayAssetReference")
            ->with($assetReference)
            ->willReturn($cachedReference);

        $files = [
            new Twig_Node_Expression_Constant("a/b/a.js", [42]),
        ];

        $node = new JavaScriptsNode($this->pathGenerator, $files, 42, "javascripts");
        $node->compile($this->compiler);

        $source = $this->compiler->getSource();
        $expectedSource = <<<RAW
// line 42
echo '<script src="' . \$this->env->getExtension("asset")->getAssetUrl("a/b/a.js") . '" integrity="sha1337-hash"></script>';\n
RAW;

        self::assertSame($expectedSource, $source);
    }


    /**
     * @group node
     * @group javascript
     * @group twig
     */
    public function testCompileSingleAssetConcatWithSubresourceIntegrity ()
    {
        $assetReference = new AssetReference("a/b/cd.js", AssetReference::TYPE_JAVASCRIPT);
        $cachedReference = new CachedReference("a/b/cd.js", "hash", "sha1337");

        $this->pathGenerator
            ->expects($this->once())
            ->method("getDisplayAssetReference")
            ->with($assetReference)
            ->willReturn($cachedReference);

        $files = [
            new Twig_Node_Expression_Binary_Concat(
                new Twig_Node_Expression_Constant("a/b/", [42]),
                new Twig_Node_Expression_Constant("cd.js", [42]),
                42
            ),
        ];

        $node = new JavaScriptsNode($this->pathGenerator, $files, 42, "javascripts");
        $node->compile($this->compiler);

        $source = $this->compiler->getSource();
        $expectedSource = <<<RAW
// line 42
echo '<script src="' . \$this->env->getExtension("asset")->getAssetUrl("a/b/cd.js") . '" integrity="sha1337-hash"></script>';\n
RAW;

        self::assertSame($expectedSource, $source);
    }


    /**
     * @group node
     * @group javascript
     * @group twig
     */
    public function testCompileMultipleAssetsWithSubresourceIntegrity ()
    {
        $assetReferenceA = new AssetReference("a/b/a.js", AssetReference::TYPE_JAVASCRIPT);
        $assetReferenceB = new AssetReference("b/c/b.js", AssetReference::TYPE_JAVASCRIPT);

        $cachedReferenceA = new CachedReference("a/b/a.js", "hashA", "sha1337");
        $cachedReferenceB = new CachedReference("b/c/b.js", "hashB", "sha42");

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
            new Twig_Node_Expression_Constant("a/b/a.js", [42]),
            new Twig_Node_Expression_Constant("b/c/b.js", [42]),
        ];

        $node = new JavaScriptsNode($this->pathGenerator, $files, 42, "javascripts");
        $node->compile($this->compiler);

        $source = $this->compiler->getSource();
        $expectedSource = <<<RAW
// line 42
echo '<script src="' . \$this->env->getExtension("asset")->getAssetUrl("a/b/a.js") . '" integrity="sha1337-hashA"></script>';
echo '<script src="' . \$this->env->getExtension("asset")->getAssetUrl("b/c/b.js") . '" integrity="sha42-hashB"></script>';\n
RAW;

        self::assertSame($expectedSource, $source);
    }
}
