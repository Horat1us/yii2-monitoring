<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Control;

use yii\di;
use yii\base;
use yii\caching;
use Horat1us\Yii\Monitoring;

/**
 * Class Cache
 * @package Horat1us\Yii\Monitoring\Control
 */
class Cache extends Monitoring\Control
{
    public const CODE_WRITE = 201;
    public const CODE_READ = 202;
    public const CODE_DELETE = 203;

    /** @var string|array|caching\CacheInterface */
    public $cache = 'cache';

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->cache = di\Instance::ensure($this->cache, caching\CacheInterface::class);
    }

    /**
     * @throws Monitoring\Exception
     */
    public function execute(): ?array
    {
        $key = 'monitoring.cache.test.' . microtime();
        /** @noinspection PhpUnhandledExceptionInspection */
        $contents = bin2hex(random_bytes(512));

        $isWitted = $this->cache->set($key, $contents, 10);
        $this->assertEquals(
            true,
            $isWitted,
            "Ошибка записи значения в кэш",
            static::CODE_WRITE
        );

        $cacheContents = $this->cache->get($key);
        $this->assertEquals(
            $contents,
            $cacheContents,
            "Ошибка чтения значения из кэша",
            static::CODE_READ
        );

        $isDeleted = $this->cache->delete($key);
        $this->assertEquals(
            true,
            $isDeleted,
            "Ошибка удаления значения из кэша",
            static::CODE_DELETE
        );

        return $this->details();
    }

    protected function details(): array
    {
        return [
            'type' => get_class($this->cache),
        ];
    }
}
