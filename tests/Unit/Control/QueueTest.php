<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control;

use Horat1us\Yii\Monitoring\Control\Queue;
use PHPUnit\Framework\TestCase;

/**
 * Class QueueTest
 * @package Horat1us\Yii\Monitoring\Tests\Unit\Control
 */
class QueueTest extends TestCase
{
    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Cache reference have to be specified as string or array
     */
    public function testFailedInitWithInvalidQueue(): void
    {
        $queue = new Queue([
            'queue' => $this->createMock(\yii\queue\db\Queue::class),
            'cache' => false
        ]);

        $queue->execute();
    }
}
