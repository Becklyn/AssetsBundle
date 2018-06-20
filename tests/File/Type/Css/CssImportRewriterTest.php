<?php

namespace Tests\Becklyn\AssetsBundle\File\Type\Css;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\File\Type\Css\CssImportRewriter;
use Becklyn\AssetsBundle\Url\AssetUrl;
use PHPUnit\Framework\TestCase;


class CssImportRewriterTest extends TestCase
{
    /**
     * Builds a CSS processor with a pathmap, that maps used asset paths to generated ones
     *
     * @param array $pathMap
     * @param bool  $isDebug
     * @return array
     */
    private function getRewriter (array $pathMap = []) : CssImportRewriter
    {
        $cache = $this->getMockBuilder(AssetsCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (!empty($pathMap))
        {
            $cache
                ->method("get")
                ->willReturnCallback(
                    function (Asset $asset) use ($pathMap)
                    {
                        $asset->setHash($pathMap[$asset->getAssetPath()] ?? null);
                        return $asset;
                    }
                );
        }

        $assetUrl = $this->getMockBuilder(AssetUrl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $assetUrl
            ->method("generateUrl")
            ->willReturn("");

        return new CssImportRewriter($cache, $assetUrl);
    }


    /**
     * Tests that quotes are preserved and whitespace is stripped
     */
    public function testNormalizationAndQuotes ()
    {
        $rewriter = $this->getRewriter([
            "@assets/img/logo.svg" => "123",
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

        self::assertSame(
            $expected,
            $rewriter->rewriteRelativeImports(new Asset("assets", "css/test.css"), $input)
        );
    }


    /**
     * Tests that the file name is correctly replaced
     */
    public function testReplace ()
    {
        $rewriter = $this->getRewriter([
            "@assets/img/logo.svg" => "123",
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

        self::assertSame(
            $expected,
            $rewriter->rewriteRelativeImports(new Asset("assets", "css/test.css"), $input)
        );
    }


    /**
     * Tests that invalid quotes are preserved
     */
    public function testInvalidQuotesPreserved ()
    {
        $rewriter = $this->getRewriter([]);

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

        self::assertSame(
            $input,
            $rewriter->rewriteRelativeImports(new Asset("assets", "css/test.css"), $input)
        );
    }


    /**
     * Tests that invalid paths are ignored and preserved
     */
    public function testInvalidPathsIgnored ()
    {
        $rewriter = $this->getRewriter([]);

        $input = <<<CSS
.first {
    background-image: url("../../../../img/logo.svg");
}
.second {
    background-image: url("../");
}
CSS;

        self::assertSame(
            $input,
            $rewriter->rewriteRelativeImports(new Asset("assets", "css/test.css"), $input)
        );
    }
}
