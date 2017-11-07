<?php

namespace Tests\Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;
use Becklyn\AssetsBundle\Twig\AssetsTwigExtension;
use PHPUnit\Framework\TestCase;


class AssetsTwigExtensionTest extends TestCase
{
    /**
     * Assert that the exposed twig functions don't change
     */
    public function testMethodNames ()
    {
        $htmlReferences = self::getMockBuilder(AssetHtmlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new AssetsTwigExtension($htmlReferences);
        $functions = \array_map(
            function (\Twig_SimpleFunction $f)
            {
                return $f->getName();
            },
            $extension->getFunctions()
        );

        self::assertContains("cssAssets", $functions);
        self::assertContains("jsAssets", $functions);
        self::assertContains("assetPath", $functions);
    }

}
