<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit\Web;

use Horat1us\Yii\Monitoring;
use PHPUnit\Framework\TestCase;
use yii\base\Module;

/**
 * Class ControllerTest
 * @package Horat1us\Yii\Monitoring\Tests\Unit\Web
 */
class ControllerTest extends TestCase
{
    /**
     * @expectedException \yii\web\NotFoundHttpException
     * @expectedExceptionMessage Control 1 not found.
     * @expectedExceptionCode -2
     */
    public function testFailedFindControl(): void
    {
        $controller = new Monitoring\Web\Controller('id', $this->createMock(Module::class));
        $controller->actionControl(1);
    }

    /**
     * @expectedException \yii\web\HttpException
     * @expectedExceptionMessage Control queue has invalid configuration
     * @expectedExceptionCode -1
     */
    public function testInvalidControlConfiguration(): void
    {
        $controller = new Monitoring\Web\Controller('id', $this->createMock(Module::class));
        $controller->controls = [
            'queue' => 'invalidControl'
        ];
        $controller->actionControl('queue');
    }

    public function testFailedActionControl(): void
    {
        $controller = new Monitoring\Web\Controller('id', $this->createMock(Module::class));
        $queue = $this->createMock(Monitoring\Control\Queue::class);
        $queue->expects($this->once())
            ->method('execute')
            ->willThrowException(new \RuntimeException('Some exception'));
        $controller->controls = [
            'queue' => $queue
        ];
        $view = $controller->actionControl('queue');

        $this->assertEquals(Monitoring\Web\View::STATE_ERROR, $view['state']);
    }

    public function testSuccessActionControl(): void
    {
        $controller = new Monitoring\Web\Controller('id', $this->createMock(Module::class));
        $queue = $this->createMock(Monitoring\Control\Queue::class);
        $queue->expects($this->once())
            ->method('execute')
            ->willReturn(['test' => 'test']);
        $controller->controls = [
            'queue' => $queue
        ];
        $view = $controller->actionControl('queue');

        $this->assertEquals(Monitoring\Web\View::STATE_OK, $view['state']);
    }

    public function testActionFullWithErroredControl(): void
    {
        $controller = new Monitoring\Web\Controller('id', $this->createMock(Module::class));
        $queue = $this->createMock(Monitoring\Control\Queue::class);
        $queue->expects($this->once())
            ->method('execute')
            ->willReturn(['test' => 'test']);
        $cache = $this->createMock(Monitoring\Control\Cache::class);
        $cache->expects($this->once())
            ->method('execute')
            ->willThrowException(new \RuntimeException('Some exception'));
        $controller->controls = [
            'queue' => $queue,
            'cache' => $cache,
        ];
        $result = $controller->actionFull();

        $this->assertEquals(Monitoring\Web\View::STATE_ERROR, $result['state']);
        $this->assertArrayHasKey('ms', $result);
        $this->assertEquals(Monitoring\Exception::class, $result['error']['type']);
    }

    public function testSuccessActionFull(): void
    {
        $controller = new Monitoring\Web\Controller('id', $this->createMock(Module::class));
        $queue = $this->createMock(Monitoring\Control\Queue::class);
        $queue->expects($this->once())
            ->method('execute')
            ->willReturn(['test' => 'test']);
        $cache = $this->createMock(Monitoring\Control\Cache::class);
        $cache->expects($this->once())
            ->method('execute')
            ->willReturn(['test' => 'test']);
        $controller->controls = [
            'queue' => $queue,
            'cache' => $cache,
        ];
        $result = $controller->actionFull();

        $this->assertEquals(Monitoring\Web\View::STATE_OK, $result['state']);
    }
}
