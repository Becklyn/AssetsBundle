<?php

namespace Tests\Becklyn\AssetsBundle\Finder;

use Becklyn\AssetsBundle\Finder\AssetsFinder;
use PHPUnit\Framework\TestCase;


class FinderTest extends TestCase
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
        $this->fixtures = dirname(__DIR__) . "/fixtures";
    }

    public function testCorrectFindings ()
    {
        $finder = new AssetsFinder($this->fixtures);

        $files = $finder->findAssets();

        self::assertCount(2, $files);
        self::assertContains("bundles/test/css/app.css", $files);
        self::assertContains("bundles/test/js/test.js", $files);
    }
}
