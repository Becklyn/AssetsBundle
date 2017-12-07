<?php

namespace Becklyn\AssetsBundle\tests\Processor;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Processor\CssProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;


class CssProcessorTest extends TestCase
{
    /**
     * Builds a CSS processor with a pathmap, that maps used asset paths to generated ones
     *
     * @param array $pathMap
     * @param bool  $isDebug
     * @return array
     */
    private function getProcessor (array $pathMap = [], bool $isDebug) : array
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

        $router = self::getMockBuilder(RouterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            new CssProcessor($cache, $router, $isDebug),
            $router
        ];
    }


    /**
     * Tests that quotes are preserved and whitespace is stripped
     */
    public function testNormalizationAndQuotes ()
    {
        /**
         * @type CssProcessor $processor
         */
        [$processor] = $this->getProcessor([
            "assets/img/logo.svg" => "123",
        ], false);

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
        /**
         * @type CssProcessor $processor
         */
        [$processor] = $this->getProcessor([
            "assets/img/logo.svg" => "123",
        ], false);

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
        /**
         * @type CssProcessor $processor
         */
        [$processor] = $this->getProcessor([], false);

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
        /**
         * @type CssProcessor $processor
         */
        [$processor] = $this->getProcessor([], false);

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


    public function testDebugRewrite ()
    {
        /**
         * @type CssProcessor $processor
         * @type \PHPUnit_Framework_MockObject_MockObject $router
         */
        [$processor, $router] = $this->getProcessor([], true);

        $router
            ->expects(self::once())
            ->method("generate")
            ->with("becklyn_assets_embed", [
                "path" => \rawurlencode("@TestBundle/img/a.jpg"),
            ])
            // just to please the type constraints in the method
            ->willReturn("a");

        $input = <<<CSS
.test {
    background-image: url("../img/a.jpg");
}
CSS;

        $processor->process("@TestBundle/css/test.css", $input);
    }
}
