<?php

namespace Becklyn\AssetsBundle\tests\Twig\TokenParser;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Path\PathGenerator;
use Becklyn\AssetsBundle\tests\BaseTest;
use Becklyn\AssetsBundle\Tests\Twig\TestableTwigParser;
use Becklyn\AssetsBundle\Twig\Extension\AssetsTwigExtension;
use Becklyn\AssetsBundle\Twig\Extension\TokenParser\StylesheetsTokenParser;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Twig_Environment;
use Twig_ExpressionParser;
use Twig_Loader_Array;
use Twig_Token;
use Twig_TokenStream;


class StylesheetsTokenParserTest extends BaseTest
{
    /**
     * @var PathGenerator|MockObject
     */
    private $pathGenerator;


    /**
     * @var Twig_Environment
     */
    private $twig;


    public function setUp ()
    {
        $this->pathGenerator = self::getMockBuilder(PathGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->twig = new Twig_Environment(new Twig_Loader_Array([]), [
            "cache" => false,
        ]);
        $this->twig->addExtension(new AssetsTwigExtension($this->pathGenerator));
    }


    /**
     * @group token-parser
     * @group stylesheet
     * @group twig
     *
     *
     * @expectedException \Twig_Error_Syntax
     * @expectedExceptionMessageRegExp /^No files were specified in the 'stylesheets' block/
     */
    public function testParseEmptyFiles ()
    {
        $token = new Twig_Token(Twig_Token::NAME_TYPE, "stylesheets", 1);

        $tokenStream = new Twig_TokenStream([
            new Twig_Token(Twig_Token::BLOCK_END_TYPE, "", 1),
            new Twig_Token(Twig_Token::EOF_TYPE, "", -1),
        ]);

        $parser = new TestableTwigParser($this->twig);
        $parser->setStream($tokenStream);

        // Circular dependencies are always fun...
        $expressionParser = new Twig_ExpressionParser($parser, $this->twig);
        $parser->setExpressionParser($expressionParser);

        $tokenParser = new StylesheetsTokenParser($this->pathGenerator);
        $tokenParser->setParser($parser);

        $assetNode = $tokenParser->parse($token);
    }


    /**
     * @group token-parser
     * @group stylesheet
     * @group twig
     */
    public function testParseSingleFile ()
    {
        $token = new Twig_Token(Twig_Token::NAME_TYPE, "stylesheets", 1);

        $tokenStream = new Twig_TokenStream([
            new Twig_Token(Twig_Token::STRING_TYPE, "a.css", 1),
            new Twig_Token(Twig_Token::BLOCK_END_TYPE, "", 1),
            new Twig_Token(Twig_Token::EOF_TYPE, "", -1),
        ]);

        $parser = new TestableTwigParser($this->twig);
        $parser->setStream($tokenStream);

        // Circular dependencies are always fun...
        $expressionParser = new Twig_ExpressionParser($parser, $this->twig);
        $parser->setExpressionParser($expressionParser);

        $tokenParser = new StylesheetsTokenParser($this->pathGenerator);
        $tokenParser->setParser($parser);

        $assetNode = $tokenParser->parse($token);

        self::assertSame(AssetReference::TYPE_STYLESHEET, $assetNode->getAssetType());
        self::assertCount(1, $assetNode->getAssetReferences());

        $assetReference = $assetNode->getAssetReferences()[0];

        self::assertSame("a.css", $assetReference->getReference());
        self::assertSame(AssetReference::TYPE_STYLESHEET, $assetReference->getType());
    }

    /**
     * @group token-parser
     * @group stylesheet
     * @group twig
     */
    public function testParseMultipleFiles ()
    {
        $token = new Twig_Token(Twig_Token::NAME_TYPE, "stylesheets", 1);

        $tokenStream = new Twig_TokenStream([
            new Twig_Token(Twig_Token::STRING_TYPE, "a.css", 1),
            new Twig_Token(Twig_Token::STRING_TYPE, "b.css", 1),
            new Twig_Token(Twig_Token::BLOCK_END_TYPE, "", 1),
            new Twig_Token(Twig_Token::EOF_TYPE, "", -1),
        ]);

        $parser = new TestableTwigParser($this->twig);
        $parser->setStream($tokenStream);

        // Circular dependencies are always fun...
        $expressionParser = new Twig_ExpressionParser($parser, $this->twig);
        $parser->setExpressionParser($expressionParser);

        $tokenParser = new StylesheetsTokenParser($this->pathGenerator);
        $tokenParser->setParser($parser);

        $assetNode = $tokenParser->parse($token);

        self::assertSame(AssetReference::TYPE_STYLESHEET, $assetNode->getAssetType());
        self::assertCount(2, $assetNode->getAssetReferences());

        $firstAssetReference = $assetNode->getAssetReferences()[0];
        $secondAssetReference = $assetNode->getAssetReferences()[1];

        self::assertSame("a.css", $firstAssetReference->getReference());
        self::assertSame(AssetReference::TYPE_STYLESHEET, $firstAssetReference->getType());

        self::assertSame("b.css", $secondAssetReference->getReference());
        self::assertSame(AssetReference::TYPE_STYLESHEET, $secondAssetReference->getType());
    }
}
