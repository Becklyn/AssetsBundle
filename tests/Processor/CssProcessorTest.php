<?php

namespace Becklyn\AssetsBundle\tests\Processor;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Processor\CssProcessor;
use PHPUnit\Framework\TestCase;


class CssProcessorTest extends TestCase
{
    /**
     * Builds a CSS processor with a pathmap, that maps used asset paths to generated ones
     *
     * @param array $pathMap
     * @return CssProcessor
     */
    private function getProcessor (array $pathMap = []) : CssProcessor
    {
        $cache = self::getMockBuilder(AssetsCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (!empty($pathMap))
        {
            $cache
                ->method("get")
                ->willReturnCallback(
                    function (string $assetPath) use ($pathMap)
                    {
                        $hash = $pathMap[$assetPath] ?? null;

                        return null !== $hash
                            ? new Asset(dirname($assetPath), \basename($assetPath), $hash)
                            : null;
                    }
                );
        }

        return new CssProcessor($cache);
    }


    /**
     * Tests that quotes are preserved and whitespace is stripped
     */
    public function testNormalizationAndQuotes ()
    {
        $processor = $this->getProcessor([
            "assets/img/logo.svg" => "123",
        ]);

        $input = <<<CSS
.first {
    background-image: url("../img/logo.svg");
}

.second {
    background-image: url('test.jpg');
}

.third {
    background-image: url(/test.jpg);
}

.first-2 {
    background-image: url(   "./test.jpg"  );
}

.second-2 {
    background-image: url(  'test.jpg'    );
}

.third-2 {
    background-image: url(   test.jpg  );
}
CSS;

        $expected = <<<CSS
.first {
    background-image: url("../img/logo.123.svg");
}

.second {
    background-image: url('test.jpg');
}

.third {
    background-image: url(/test.jpg);
}

.first-2 {
    background-image: url("./test.jpg");
}

.second-2 {
    background-image: url('test.jpg');
}

.third-2 {
    background-image: url(test.jpg);
}
CSS;

        self::assertSame($expected, $processor->process("assets/css/test.css", $input));
    }

    /**
     * Tests that quotes are preserved and whitespace is stripped
     */
    public function testReplace ()
    {
        $processor = $this->getProcessor([
            "assets/img/logo.svg" => "123",
        ]);

        $input = <<<CSS
.first {
    background-image: url("../img/logo.svg");
}
CSS;

        $expected = <<<CSS
.first {
    background-image: url("../img/logo.123.svg");
}
CSS;

        self::assertSame($expected, $processor->process("assets/css/test.css", $input));
    }


    public function testInvalidCUrlPreserved ()
    {
        $processor = $this->getProcessor();

        $input = <<<CSS
.first {
    background-image: url("../img/logo.svg);
    background-image: url('../img/logo.svg);
    background-image: url(../img/logo.svg");
    background-image: url(../img/logo.svg');
    background-image: url("../img/logo.svg');
    background-image: url('../img/logo.svg");
}
CSS;

        self::assertSame($input, $processor->process("assets/css/test.css", $input));
    }


    public function testInvalidPathsIgnored ()
    {
        $processor = $this->getProcessor();

        $input = <<<CSS
.first {
    background-image: url("../../../../img/logo.svg");
}
.second {
    background-image: url("../");
}
CSS;

        self::assertSame($input, $processor->process("css/test.css", $input));
    }
}
