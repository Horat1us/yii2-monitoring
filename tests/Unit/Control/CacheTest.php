<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control;

use Horat1us\Yii\Monitoring;
use PHPUnit\Framework\TestCase;
use yii\caching\ArrayCache;
use yii\caching\Cache;
use yii\caching\CacheInterface;

/**
 * Class CacheTest
 * @package Horat1us\Yii\Monitoring\Tests\Unit\Control
 */
class CacheTest extends TestCase
{
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
        $cacheMock = new class extends ArrayCache implements CacheInterface
        {
            protected $value;

            public function set($key, $value, $duration = null, $dependency = null)
            {
                $this->value = $value;
                return true;
            }

            public function get($key)
            {
                return $this->value;
            }

            public function delete($key)
            {
                return false;
            }
        };

        $cache = new Monitoring\Control\Cache(['cache' => $cacheMock]);

        $this->expectException(Monitoring\Exception::class);
        $this->expectExceptionMessage("Ошибка удаления значения из кэша");
        $this->expectExceptionCode(Monitoring\Control\Cache::CODE_DELETE);

        $cache->execute();
    }

    public function testSuccess(): void
    {
        $cacheMock = new class extends ArrayCache implements CacheInterface
        {
            protected $value;

            public function set($key, $value, $duration = null, $dependency = null)
            {
                $this->value = $value;
                return true;
            }

            public function get($key)
            {
                return $this->value;
            }

            public function delete($key)
            {
                return true;
            }
        };

        $cache = new Monitoring\Control\Cache(['cache' => $cacheMock]);
        $details = $cache->execute();

        $this->assertNotEmpty($details['type']);
    }
}
