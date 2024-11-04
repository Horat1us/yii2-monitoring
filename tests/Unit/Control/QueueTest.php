<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control;

use Horat1us\Yii\Monitoring\Control\Queue;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use yii\caching\ArrayCache;
use yii\base;

class QueueTest extends TestCase
{
    use PHPMock;

    public function testFailedInitWithInvalidQueue(): void
    {
        $queue = new Queue([
            'queue' => $this->createMock(\yii\queue\db\Queue::class),
            'cache' => false
        ]);

        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Cache reference have to be specified as string or array');

        $queue->execute();
    }

    public function testFailedAssert(): void
    {
        $microtime = $this->getFunctionMock("Horat1us\\Yii\\Monitoring\\Control", 'microtime');
        $microtime->expects($this->once())
            ->willReturn('123456');
        $bin2hex = $this->getFunctionMock("Horat1us\\Yii\\Monitoring\\Control", 'bin2hex');
        $bin2hex->expects($this->once())
            ->willReturn('test');

        $queueMock = $this->createMock(\yii\queue\db\Queue::class);
        $queueMock->expects($this->once())
            ->method('push')
            ->willReturn('id');
        $queueMock->expects($this->once())
            ->method('isDone')
            ->with('id')
            ->willReturn(true);
        $cacheMock = $this->createMock(ArrayCache::class);
        $cacheMock->expects($this->once())
            ->method('get')
            ->with('monitoring.queue.test.123456')
            ->willReturn('invalid');
        \Yii::$container->set('cache', $cacheMock);
        $queue = new Queue([
            'queue' => $queueMock,
            'cache' => 'cache',
            'timeout' => 4
        ]);

        $this->expectException(\Horat1us\Yii\Monitoring\Exception::class);
        $this->expectExceptionMessage('Ошибка чтения значения записанного из очереди');
        $this->expectExceptionCode(301);

        $queue->execute();
    }

    public function testFailedAssertWithTwoIterations(): void
    {
        $microtime = $this->getFunctionMock("Horat1us\\Yii\\Monitoring\\Control", 'microtime');
        $microtime->expects($this->once())
            ->willReturn('123456');
        $bin2hex = $this->getFunctionMock("Horat1us\\Yii\\Monitoring\\Control", 'bin2hex');
        $bin2hex->expects($this->once())
            ->willReturn('test');

        $queueMock = $this->createMock(\yii\queue\db\Queue::class);
        $queueMock->expects($this->once())
            ->method('push')
            ->willReturn('id');
        $queueMock->expects($this->exactly(2))
            ->method('isDone')
            ->with('id')
            ->willReturn(false, true);
        $cacheMock = $this->createMock(ArrayCache::class);
        $cacheMock->expects($this->once())
            ->method('get')
            ->with('monitoring.queue.test.123456')
            ->willReturn('invalid');
        \Yii::$container->set('cache', $cacheMock);
        $queue = new Queue([
            'queue' => $queueMock,
            'cache' => 'cache',
            'timeout' => 4
        ]);

        $this->expectException(\Horat1us\Yii\Monitoring\Exception::class);
        $this->expectExceptionMessage('Ошибка чтения значения записанного из очереди');
        $this->expectExceptionCode(301);

        $queue->execute();
    }

    public function testFailedAllAttempts(): void
    {
        $microtime = $this->getFunctionMock("Horat1us\\Yii\\Monitoring\\Control", 'microtime');
        $microtime->expects($this->once())
            ->willReturn('123456');
        $bin2hex = $this->getFunctionMock("Horat1us\\Yii\\Monitoring\\Control", 'bin2hex');
        $bin2hex->expects($this->once())
            ->willReturn('test');

        $queueMock = $this->createMock(\yii\queue\db\Queue::class);
        $queueMock->expects($this->once())
            ->method('push')
            ->willReturn('id');
        $queueMock->expects($this->once())
            ->method('isDone')
            ->with('id')
            ->willReturn(false);
        $cacheMock = $this->createMock(ArrayCache::class);
        \Yii::$container->set('cache', $cacheMock);
        $queue = new Queue([
            'queue' => $queueMock,
            'cache' => 'cache',
            'timeout' => 1
        ]);

        $this->expectException(\Horat1us\Yii\Monitoring\Exception::class);
        $this->expectExceptionMessage('Ошибка выполнения задачи в очереди');
        $this->expectExceptionCode(302);

        $queue->execute();
    }

    public function testSuccess(): void
    {
        $microtime = $this->getFunctionMock("Horat1us\\Yii\\Monitoring\\Control", 'microtime');
        $microtime->expects($this->once())
            ->willReturn('123456');
        $bin2hex = $this->getFunctionMock("Horat1us\\Yii\\Monitoring\\Control", 'bin2hex');
        $bin2hex->expects($this->once())
            ->willReturn('test');

        $queueMock = $this->createMock(\yii\queue\db\Queue::class);
        $queueMock->expects($this->once())
            ->method('push')
            ->willReturn('id');
        $queueMock->expects($this->once())
            ->method('isDone')
            ->with('id')
            ->willReturn(true);
        $cacheMock = $this->createMock(ArrayCache::class);
        $cacheMock->expects($this->once())
            ->method('get')
            ->with('monitoring.queue.test.123456')
            ->willReturn('test');
        \Yii::$container->set('cache', $cacheMock);
        $queue = new Queue([
            'queue' => $queueMock,
            'cache' => 'cache',
            'timeout' => 1
        ]);

        $this->assertArrayHasKey('type', $queue->execute());
    }
}
