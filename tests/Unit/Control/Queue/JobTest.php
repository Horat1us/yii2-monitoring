<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control\Queue;

use Horat1us\Yii\Monitoring\Control\Queue\Job;
use PHPUnit\Framework\TestCase;
use yii\caching\ArrayCache;
use yii\db\Connection;
use yii\mutex\FileMutex;
use yii\queue\db\Queue;

/**
 * Class JobTest
 * @package Horat1us\Yii\Monitoring\Tests\Unit\Control\Queue
 */
class JobTest extends TestCase
{
    public function testExecute(): void
    {
        \Yii::$container->set('db', $this->createMock(Connection::class));
        \Yii::$container->set('mutex', $this->createMock(FileMutex::class));
        $value = 'test-value';
        $key = 'test-key';
        $mock = $this->createMock(ArrayCache::class);
        $mock->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($value);
        $job = new Job([
            'key' => $key,
            'cache' => $mock,
            'value' => $value,
            'duration' => 10,
        ]);
        $job->execute(new Queue());
        /** @var ArrayCache $cache */
        $cache = $job->cache;
        $this->assertEquals($value, $cache->get($key));
    }
}
