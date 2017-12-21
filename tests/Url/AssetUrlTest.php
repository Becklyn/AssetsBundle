<?php

namespace Becklyn\AssetsBundle\tests\Url;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Url\AssetUrl;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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

        $requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            new AssetUrl($registry, $router, $requestStack, $isDebug),
            $router,
            $requestStack,
            $registry
        ];
    }


    public function testDev ()
    {
        /**
         * @type AssetUrl $assetUrl
         * @type \PHPUnit_Framework_MockObject_MockObject $router
         */
        [$assetUrl, $router] = $this->buildObject(true);

        $router
            ->expects(self::once())
            ->method("generate")
            ->with("becklyn_assets_embed", ["path" => \rawurlencode("@test/abc")])
            ->willReturn("example");

        $assetUrl->generateUrl("@test/abc");
    }


    public function testProd ()
    {
        /**
         * @type AssetUrl $assetUrl
         * @type \PHPUnit_Framework_MockObject_MockObject $router
         * @type \PHPUnit_Framework_MockObject_MockObject $requestStack
         * @type \PHPUnit_Framework_MockObject_MockObject $registry
         */
        [$assetUrl, $router, $requestStack, $registry] = $this->buildObject(false);

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request
            ->expects(self::once())
            ->method("getBaseUrl")
            ->willReturn("/base-url");

        $requestStack
            ->expects(self::once())
            ->method("getMasterRequest")
            ->willReturn($request);

        $registry
            ->expects(self::once())
            ->method("get")
            ->with("@test/abc")
            ->willReturn(new Asset("out", "test.jpg", "hash"));

        $actual = $assetUrl->generateUrl("@test/abc");
        self::assertEquals("/base-url/out/test.hash.jpg", $actual);
    }


    /**
     * @expectedException \Becklyn\AssetsBundle\Exception\AssetsException
     * @expectedExceptionMessage Can't embed asset '@test/abc' without request.
     */
    public function testProdWithoutRequest ()
    {
        /**
         * @type AssetUrl $assetUrl
         * @type \PHPUnit_Framework_MockObject_MockObject $router
         * @type \PHPUnit_Framework_MockObject_MockObject $requestStack
         */
        [$assetUrl, $router, $requestStack] = $this->buildObject(false);

        $requestStack
            ->expects(self::once())
            ->method("getMasterRequest")
            ->willReturn(null);

        $assetUrl->generateUrl("@test/abc");
    }

}
