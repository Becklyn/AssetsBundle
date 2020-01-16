<?php declare(strict_types=1);

namespace Tests\Becklyn\AssetsBundle\Url;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\File\FileLoader;
use Becklyn\AssetsBundle\Url\AssetUrl;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;

class AssetUrlTest extends TestCase
{
    private function buildObject (bool $isDebug)
    {
        $registry = $this->getMockBuilder(AssetsRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $router = $this->getMockBuilder(RouterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileLoader = $this->getMockBuilder(FileLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            new AssetUrl($registry, $router, $fileLoader, $isDebug, null),
            $router,
            $registry,
            $fileLoader,
        ];
    }


    public function testDev () : void
    {
        /**
         * @var AssetUrl
         * @var \PHPUnit_Framework_MockObject_MockObject $router
         * @var \PHPUnit_Framework_MockObject_MockObject $registry
         * @var \PHPUnit_Framework_MockObject_MockObject $fileLoader
         */
        [$assetUrl, $router, $registry, $fileLoader] = $this->buildObject(true);

        $asset = new Asset("namespace", "test.jpg");

        $fileLoader
            ->method("fileForAssetExists")
            ->willReturn(true);

        $router
            ->expects(self::once())
            ->method("generate")
            ->with("becklyn_assets.embed", [
                "namespace" => "namespace",
                "path" => "test.jpg",
            ])
            ->willReturn("example");

        $assetUrl->generateUrl($asset);
    }


    public function testProd () : void
    {
        /**
         * @var AssetUrl
         * @var \PHPUnit_Framework_MockObject_MockObject $router
         * @var \PHPUnit_Framework_MockObject_MockObject $registry
         */
        [$assetUrl, $router, $registry] = $this->buildObject(false);

        $asset = new Asset("test", "abc.jpg");

        $registry
            ->expects(self::once())
            ->method("get")
            ->with($asset)
            ->willReturnCallback(function () {
                $result = new Asset("out", "test.jpg");
                $result->setHash("hash");
                return $result;
            });

        $router
            ->expects(self::once())
            ->method("generate")
            ->with("becklyn_assets.embed", [
                "namespace" => "out",
                "path" => "test.hash.jpg",
            ])
            ->willReturn("");

        $assetUrl->generateUrl($asset);
    }


    /**
     * Missing file in prod WITHOUT logger:.
     *
     * just returns the default path
     */
    public function testMissingFileInProd () : void
    {
        /**
         * @var AssetUrl
         * @var \PHPUnit_Framework_MockObject_MockObject $router
         * @var \PHPUnit_Framework_MockObject_MockObject $registry
         */
        [$assetUrl, $router, $registry] = $this->buildObject(false);

        $asset = new Asset("test", "abc.jpg");

        $registry
            ->expects(self::once())
            ->method("get")
            ->with($asset)
            ->willThrowException(new AssetsException());

        $router
            ->expects(self::once())
            ->method("generate")
            ->with("becklyn_assets.embed", [
                "namespace" => $asset->getNamespace(),
                "path" => $asset->getFilePath(),
            ])
            ->willReturn("");

        $assetUrl->generateUrl($asset);
    }


    /**
     * Missing file in prod WITH logger:.
     *
     * logs an error and returns the default path
     */
    public function testMissingFileInProdWithLogger () : void
    {
        $registry = $this->getMockBuilder(AssetsRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $router = $this->getMockBuilder(RouterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileLoader = $this->getMockBuilder(FileLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $assetUrl = new AssetUrl($registry, $router, $fileLoader, false, $logger);

        $asset = new Asset("test", "abc.jpg");

        $registry
            ->expects(self::once())
            ->method("get")
            ->with($asset)
            ->willThrowException(new AssetsException());

        $router
            ->method("generate")
            ->willReturn("");

        $logger
            ->expects(self::once())
            ->method("error");

        $assetUrl->generateUrl($asset);
    }


    /**
     * Missing file in dev:.
     *
     * Throw exception
     *
     * @expectedException \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function testMissingFileInDev () : void
    {
        /**
         * @var AssetUrl $assetUrl
         * @var \PHPUnit_Framework_MockObject_MockObject $router
         * @var \PHPUnit_Framework_MockObject_MockObject $registry
         * @var \PHPUnit_Framework_MockObject_MockObject $fileLoader
         */
        [$assetUrl, $router, $registry, $fileLoader] = $this->buildObject(true);

        $fileLoader
            ->method("fileForAssetExists")
            ->willReturn(false);

        $fileLoader
            ->method("getFilePath")
            ->willReturn("/some/path");

        $this->expectException(AssetsException::class);
        $this->expectExceptionMessage("Asset '@test/abc.jpg' not found at '/some/path'.");

        $asset = new Asset("test", "abc.jpg");
        $assetUrl->generateUrl($asset);
    }
}
