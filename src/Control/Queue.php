<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Control;

use yii\di;
use yii\base;
use yii\caching;
use Horat1us\Yii\Monitoring;

/**
 * Class Queue
 * @package Horat1us\Yii\Monitoring\Control
 */
class Queue extends Monitoring\Control
{
    public const CODE_VALUE = 301;
    public const CODE_JOB = 302;

    /** @var int Queue job execution timeout, seconds */
    public $timeout = 10;

    /** @var string|array|\yii\queue\Queue */
    public $queue = 'queue';

    /**
     * String or array cache reference
     * @see caching\CacheInterface
     * @see \yii\di\Instance::ensure()
     *
     * @var string|array
     */
    public $cache = 'cache';

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->queue = di\Instance::ensure($this->queue, \yii\queue\Queue::class);
    }

    public function execute(): ?array
    {
        if (!is_array($this->cache) && !is_string($this->cache)) {
            throw new base\InvalidConfigException(
                "Cache reference have to be specified as string or array"
            );
        }
        /** @var caching\CacheInterface $cache */
        $cache = di\Instance::ensure($this->cache, caching\CacheInterface::class);

        $key = 'monitoring.queue.test.' . microtime();
        $value = bin2hex(random_bytes(512));

        $job = new Queue\Job(compact('key', 'value') + [
                'cache' => $this->cache,
                'duration' => $this->timeout + 1,
            ]);

        $id = $this->queue->push($job);

        for ($seconds = 0; $seconds < $this->timeout; $seconds++) {
            sleep(1);
            if (!$this->queue->isDone($id)) {
                continue;
            }

            $cacheValue = $cache->get($key);
            $this->assertEquals(
                $value,
                $cacheValue,
                'Ошибка чтения значения записанного из очереди',
                static::CODE_VALUE
            );

            return $this->details();
        }

        throw new Monitoring\Exception(
            "Ошибка выполнения задачи в очереди",
            static::CODE_JOB,
            $this->details()
        );
    }

    protected function details(): array
    {
        return [
            'type' => get_class($this->queue),
        ];
    }
}
