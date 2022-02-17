<?php declare(strict_types=1);

namespace Tests\Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Helper\AssetHelper;
use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;
use Becklyn\AssetsBundle\Twig\AssetsTwigExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

class AssetsTwigExtensionTest extends TestCase
{
    use ArraySubsetAsserts;

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
            static fn (TwigFunction $f) => $f->getName(),
            $extension->getFunctions()
        );

        $this->assertArraySubset([
            "asset",
            "asset_inline",
            "assets_link",
        ], $functions);
    }
}
