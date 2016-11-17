<?php

namespace Becklyn\AssetsBundle\tests;


abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns the path to the fixtures directory
     *
     * @param string|null $path
     *
     * @return string
     */
    protected function getFixturesDirectory (string $path = null)
    {
        $fixturesDir = __DIR__ . "/fixtures";

        if (null !== $path)
        {
            $fixturesDir .= "/" . trim($path, "/");
        }

        return $fixturesDir;
    }
}
