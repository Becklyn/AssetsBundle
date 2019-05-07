<?php declare(strict_types=1);

namespace Tests\Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\File\FileTypeRegistry;
use Becklyn\AssetsBundle\File\Type\GenericFile;
use Becklyn\AssetsBundle\Storage\AssetStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Tests\Becklyn\AssetsBundle\CreateHashedAssetTrait;

class AssetsRegistryTest extends TestCase
{
    use CreateHashedAssetTrait;

    private function prepare ()
    {
        $cache = $this->getMockBuilder(AssetsCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(AssetStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry = new AssetsRegistry(new ServiceLocator([
            AssetsCache::class => function () use ($cache) { return $cache; },
            AssetStorage::class => function () use ($storage) { return $storage; },
            FileTypeRegistry::class => function () { return new FileTypeRegistry(new GenericFile()); },
        ]));
        return [$registry, $cache, $storage];
    }


    public function testAutomaticGeneration () : void
    {
        /**
         * @var AssetsRegistry
         * @var \PHPUnit_Framework_MockObject_MockObject|AssetsCache  $cache
         * @var \PHPUnit_Framework_MockObject_MockObject|AssetStorage $storage
         */
        [$registry, $cache, $storage] = $this->prepare();

        $asset = $this->createdHashedAsset("test", "test", "test");

        $storage
            ->expects(self::once())
            ->method("import")
            ->with($asset)
            ->willReturn($asset);

        self::assertSame($asset, $registry->get($asset));
    }


    public function testClear () : void
    {
        /**
         * @var AssetsRegistry
         * @var \PHPUnit_Framework_MockObject_MockObject|AssetsCache  $cache
         * @var \PHPUnit_Framework_MockObject_MockObject|AssetStorage $storage
         */
        [$registry, $cache, $storage] = $this->prepare();

        // check that cache is cleared
        $cache
            ->expects(self::once())
            ->method("clear");

        // check that actual cache clearer is called
        $storage
            ->expects(self::once())
            ->method("removeAllStoredFiles");

        // clear cache
        $registry->clear();
    }
}
