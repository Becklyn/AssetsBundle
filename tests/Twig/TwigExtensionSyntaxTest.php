<?php

namespace Becklyn\AssetsBundle\tests\Twig;

use Becklyn\AssetsBundle\Path\PathGenerator;
use Becklyn\AssetsBundle\tests\BaseTest;
use Becklyn\AssetsBundle\Twig\Extension\AssetsTwigExtension;


class TwigExtensionSyntaxTest extends BaseTest
{
    /** @var \Twig_Environment */
    private $twig;


    public function setUp ()
    {
        $fixturesDir = $this->getFixturesDirectory("templates");

        $this->twig = new \Twig_Environment(new \Twig_Loader_Filesystem($fixturesDir), [
            "cache" => false,
        ]);

        $pathGenerator = self::getMockBuilder(PathGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->twig->addExtension(new AssetsTwigExtension($pathGenerator));
    }



    /**
     * @expectedException \Twig_Error_Syntax
     * @expectedExceptionMessageRegExp /^No files were specified in the 'javascripts' block/
     */
    public function testEmptyJavascriptsTag ()
    {
        $this->twig->render("empty_javascripts.twig");
    }


    /**
     * @expectedException \Twig_Error_Syntax
     * @expectedExceptionMessageRegExp /^No files were specified in the 'stylesheets' block/
     */
    public function testEmptyStylesheetsTag ()
    {
        $this->twig->render("empty_stylesheets.twig");
    }
}
