<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\tests\Namespaces;

use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use PHPUnit\Framework\TestCase;


class NamespaceRegistryTest extends TestCase
{
    /**
     * @var string
     */
    private $fixtures;


    /**
     * @inheritdoc
     */
    public function setUp ()
    {
        $this->fixtures = dirname(__DIR__) . "/fixtures/public";
    }


    public function testValid ()
    {
        $namespaces = new NamespaceRegistry();
        $path = "{$this->fixtures}/bundles";

        self::assertCount(0, $namespaces);
        $namespaces->addNamespace("bundles", $path);
        self::assertSame($path, $namespaces->getPath("bundles"));
        self::assertCount(1, $namespaces);
    }


    public function testMissingWithFail ()
    {
        $this->expectException(AssetsException::class);
        $namespaces = new NamespaceRegistry();
        $namespaces->addNamespace("missing", "{$this->fixtures}/doesnt_exist");
    }


    public function testMissingWithoutFail ()
    {
        $namespaces = new NamespaceRegistry();
        $namespaces->addNamespace("missing", "{$this->fixtures}/doesnt_exist", NamespaceRegistry::IGNORE_MISSING);
        self::assertTrue(true, "No exception till here.");
    }
}
