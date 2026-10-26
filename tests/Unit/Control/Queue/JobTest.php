<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control\Queue;

use Horat1us\Yii\Monitoring\Control\Queue\Job;
use PHPUnit\Framework\TestCase;
use yii\caching\CacheInterface;
use yii\queue\Queue;

class JobTest extends TestCase
{
    public function testExecute(): void
    {
        $value = 'test-value';
        $key = 'test-key';
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('set')
            ->with($key, $value, 10)
            ->willReturn(true);
        $job = new Job([
            'key' => $key,
            'cache' => $cache,
            'value' => $value,
            'duration' => 10,
        ]);
        $job->execute($this->createStub(Queue::class));
    }
}
