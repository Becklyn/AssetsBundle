<?php declare(strict_types=1);

namespace Tests\Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Helper\AssetHelper;
use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;
use Becklyn\AssetsBundle\Twig\AssetsTwigExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

class AssetsTwigExtensionTest extends TestCase
{
    /**
     * Assert that the exposed twig functions don't change.
     */
    public function testMethodNames () : void
    {
        $htmlReferences = $this->getMockBuilder(AssetHtmlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper = $this->getMockBuilder(AssetHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new AssetsTwigExtension($htmlReferences, $helper);
        $functions = \array_map(
            function (TwigFunction $f)
            {
                return $f->getName();
            },
            $extension->getFunctions()
        );

        self::assertArraySubset([
            "asset",
            "asset_inline",
            "assets_link",
        ], $functions);
    }
}
