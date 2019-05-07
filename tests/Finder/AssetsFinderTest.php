<?php declare(strict_types=1);

namespace Tests\Becklyn\AssetsBundle\Finder;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Finder\AssetsFinder;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use PHPUnit\Framework\TestCase;

class AssetsFinderTest extends TestCase
{
    /**
     * @var string
     */
    private $fixtures;


    /**
     * @inheritdoc
     */
    protected function setUp () : void
    {
        $this->fixtures = \dirname(__DIR__) . "/fixtures/public";
    }

    public function testCorrectFindings () : void
    {
        $namespaces = new NamespaceRegistry([
            "bundles" => "{$this->fixtures}/bundles",
        ]);

        $finder = new AssetsFinder($namespaces);
        $assets = $finder->findAssets();
        self::assertCount(2, $assets);

        $assetPaths = \array_map(function (Asset $asset) { return $asset->getAssetPath(); }, $assets);

        self::assertContains("@bundles/test/css/app.css", $assetPaths);
        self::assertContains("@bundles/test/js/test.js", $assetPaths);
    }
}
