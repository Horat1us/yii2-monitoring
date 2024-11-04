<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control;

use Horat1us\Yii\Monitoring;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use yii\caching\ArrayCache;

/**
 * Class CacheTest
 * @package Horat1us\Yii\Monitoring\Tests\Unit\Control
 */
class CacheTest extends TestCase
{
    use PHPMock;

    protected function setUp(): void
    {
        $microtime = $this->getFunctionMock("Horat1us\\Yii\\Monitoring\\Control", 'microtime');
        $microtime->expects($this->once())
            ->willReturn('123456');
        $bin2hex = $this->getFunctionMock("Horat1us\\Yii\\Monitoring\\Control", 'bin2hex');
        $bin2hex->expects($this->once())
            ->willReturn('test');
    }

    public function testFailedWrite(): void
    {
        $cacheMock = $this->createMock(ArrayCache::class);
        $cacheMock->expects($this->once())
            ->method('set')
            ->withAnyParameters()
            ->willReturn(false);

        $cache = new Monitoring\Control\Cache(['cache' => $cacheMock]);

        $this->expectException(Monitoring\Exception::class);
        $this->expectExceptionMessage('Ошибка записи значения в кэш');
        $this->expectExceptionCode(Monitoring\Control\Cache::CODE_WRITE);

        $cache->execute();
    }

    public function testFailedRead(): void
    {
        $cacheMock = $this->createMock(ArrayCache::class);
        $cacheMock->expects($this->once())
            ->method('set')
            ->withAnyParameters()
            ->willReturn(true);
        $cacheMock->expects($this->once())
            ->method('get')
            ->withAnyParameters()
            ->willReturn('invalid');

        $cache = new Monitoring\Control\Cache(['cache' => $cacheMock]);

        $this->expectException(Monitoring\Exception::class);
        $this->expectExceptionMessage("Ошибка чтения значения из кэша");
        $this->expectExceptionCode(Monitoring\Control\Cache::CODE_READ);

        $cache->execute();
    }

    public function testFailedDelete(): void
    {
        $cacheMock = $this->createMock(ArrayCache::class);
        $cacheMock->expects($this->once())
            ->method('set')
            ->with('monitoring.cache.test.123456', 'test', 10)
            ->willReturn(true);
        $cacheMock->expects($this->once())
            ->method('get')
            ->with('monitoring.cache.test.123456')
            ->willReturn('test');
        $cacheMock->expects($this->once())
            ->method($this->equalTo('delete'))
            ->with('monitoring.cache.test.123456')
            ->willReturn(false);

        $cache = new Monitoring\Control\Cache(['cache' => $cacheMock]);

        $this->expectException(Monitoring\Exception::class);
        $this->expectExceptionMessage("Ошибка удаления значения из кэша");
        $this->expectExceptionCode(Monitoring\Control\Cache::CODE_DELETE);

        $cache->execute();
    }

    public function testSuccess(): void
    {
        $cacheMock = $this->createMock(ArrayCache::class);
        $cacheMock->expects($this->once())
            ->method('set')
            ->with('monitoring.cache.test.123456', 'test', 10)
            ->willReturn(true);
        $cacheMock->expects($this->once())
            ->method('get')
            ->with('monitoring.cache.test.123456')
            ->willReturn('test');
        $cacheMock->expects($this->once())
            ->method($this->equalTo('delete'))
            ->with('monitoring.cache.test.123456')
            ->willReturn(true);

        $cache = new Monitoring\Control\Cache(['cache' => $cacheMock]);
        $details = $cache->execute();

        $this->assertMatchesRegularExpression('/Mock_ArrayCache_[a-z0-9]+/', $details['type']);
    }
}
