<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Control\Queue;

use yii\di;
use yii\base;
use yii\queue;
use yii\caching;

/**
 * Class Job
 * @package Horat1us\Yii\Monitoring\Control\Queue
 */
class Job extends base\BaseObject implements queue\JobInterface
{
    /** @var string */
    public $key;

    /** @var string */
    public $value;

    /** @var int */
    public $duration;

    /** @var string|array */
    public $cache;

    /**
     * @param queue\Queue $queue
     * @throws base\InvalidConfigException
     */
    public function execute($queue): void
    {
        /** @var caching\CacheInterface $cache */
        $cache = di\Instance::ensure($this->cache, caching\CacheInterface::class);
        $cache->set($this->key, $this->value, $this->duration);
    }
}
