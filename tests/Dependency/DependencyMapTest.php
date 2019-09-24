<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\tests\Dependency;

use Becklyn\AssetsBundle\Data\AssetEmbed;
use Becklyn\AssetsBundle\Dependency\DependencyMap;
use PHPUnit\Framework\TestCase;

class DependencyMapTest extends TestCase
{
    /**
     * @var DependencyMap
     */
    private $map;


    /**
     * @inheritDoc
     */
    protected function setUp () : void
    {
        $this->map = new DependencyMap([
            "a" => ["1", "2", "a-dep"],
            "b" => ["1", "b-dep"],
            "c.js" => ["1", "c_modern"],
            "_legacy.c.js" => ["1", "c_legacy"],
            "d.js" => ["1", "d_legacy"],
            "_modern.d.js" => ["1", "d_modern"],
        ]);
    }


    /**
     * Tests the correct order and imports.
     */
    public function testUniqueAndCorrectOrder () : void
    {
        $load = $this->map->getImportsWithDependencies(["a", "b"]);
        $this->assertAssetEmbedOrder(["1", "2", "a-dep", "b-dep"], $load);
    }


    /**
     * Test loading with missing dependency map entry.
     */
    public function testMissing () : void
    {
        $load = $this->map->getImportsWithDependencies(["a", "c", "b"]);
        $this->assertAssetEmbedOrder(["1", "2", "a-dep", "c", "b-dep"], $load);
    }


    /**
     * @param array $expected
     * @param array $embeds
     */
    private function assertAssetEmbedOrder (array $expected, array $embeds)
    {
        $prepared = \array_map(
            function (AssetEmbed $embed)
            {
                return $embed->getAssetPath();
            },
            $embeds
        );

        self::assertSame($expected, \array_values($prepared));
    }


    /**
     *
     */
    public function testModernBuilds ()
    {
        $load = $this->map->getImportsWithDependencies(["c.js"]);

        $prepared = \array_map(
            function (AssetEmbed $embed)
            {
                return $embed->getAssetPath();
            },
            $load
        );

        self::assertContains("1", $prepared);
        self::assertContains("c_modern", $prepared);
        self::assertContains("c_legacy", $prepared);
    }


    /**
     *
     */
    public function testDuplicatedDependenciesBuilds ()
    {
        $load = $this->map->getImportsWithDependencies(["d.js"]);

        self::assertArrayHasKey("1", $load);
        self::assertArrayHasKey("d_legacy", $load);
        self::assertArrayHasKey("d_modern", $load);

        self::assertSame("module", $load["d_modern"]->getAttributes()->get("type"));
        self::assertSame(true, $load["d_legacy"]->getAttributes()->get("nomodule"));

        // as 1 is a shared dependency of modern + legacy builds, it should not have any attribute.
        self::assertNull($load["1"]->getAttributes()->get("type"));
        self::assertNull($load["1"]->getAttributes()->get("nomodule"));
    }
}
