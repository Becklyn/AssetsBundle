<?php

namespace Becklyn\AssetsBundle\tests\Processor;

use Becklyn\AssetsBundle\Processor\AssetProcessorInterface;
use Becklyn\AssetsBundle\Processor\ProcessorRegistry;
use PHPUnit\Framework\TestCase;


class ProcessorRegistryTest extends TestCase
{
    public function testGetter ()
    {
        $cssProcessor = self::getMockBuilder(AssetProcessorInterface::class)
            ->getMock();

        $registry = new ProcessorRegistry([
            "css" => $cssProcessor,
        ]);

        self::assertEquals($cssProcessor, $registry->get("test.css"));
        self::assertTrue($registry->has("test.css"));
        self::assertNull($registry->get("test.js"));
        self::assertFalse($registry->has("test.js"));
    }
    public function testEmpty ()
    {
        $registry = new ProcessorRegistry([]);

        self::assertNull($registry->get("test.css"));
        self::assertFalse($registry->has("test.css"));
        self::assertNull($registry->get("test.js"));
        self::assertFalse($registry->has("test.js"));
    }
}
