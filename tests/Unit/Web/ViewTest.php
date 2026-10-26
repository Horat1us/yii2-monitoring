<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit\Web;

use Horat1us\Yii\Monitoring;
use PHPUnit\Framework\TestCase;

/**
 * Class ViewTest
 * @package Horat1us\Yii\Monitoring\Tests\Unit\Web
 */
class ViewTest extends TestCase
{
    public function testSuccess(): void
    {
        $before = microtime(true);
        $view = new Monitoring\Web\View();
        usleep(100000);

        $details = ['someDetails'];
        $clonedView = $view->success($details);
        $after = microtime(true);
        $clonedViewData = $clonedView->jsonSerialize();

        $this->assertNotSame($clonedView, $view);
        $this->assertMeasuredTime($before, $after, $clonedViewData);
        $this->assertEquals(Monitoring\Web\View::STATE_OK, $clonedViewData['state']);
        $this->assertEquals($details, $clonedViewData['details']);
    }

    public function testFail(): void
    {
        $before = microtime(true);
        $view = new Monitoring\Web\View();
        usleep(100000);

        $exception = new \RuntimeException('Some exception');
        $clonedView = $view->fail($exception);
        $after = microtime(true);
        $clonedViewData = $clonedView->jsonSerialize();

        $this->assertNotSame($clonedView, $view);

        $this->assertMeasuredTime($before, $after, $clonedViewData);
        $this->assertEquals(Monitoring\Web\View::STATE_ERROR, $clonedViewData['state']);
        $this->assertEquals(
            [
                'type' => get_class($exception),
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ],
            $clonedViewData['error']
        );
    }

    public function testFailWithMonitorException(): void
    {
        $before = microtime(true);
        $view = new Monitoring\Web\View();
        usleep(100000);

        $details = ['someDetails'];
        $exception = new Monitoring\Exception('Some exception', -1, $details);
        $clonedView = $view->fail($exception);
        $after = microtime(true);
        $clonedViewData = $clonedView->jsonSerialize();

        $this->assertNotSame($clonedView, $view);

        $this->assertMeasuredTime($before, $after, $clonedViewData);
        $this->assertEquals(Monitoring\Web\View::STATE_ERROR, $clonedViewData['state']);
        $this->assertEquals(
            [
                'type' => get_class($exception),
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'details' => $details,
            ],
            $clonedViewData['error']
        );
    }

    public function testFailedJsonSerialize(): void
    {
        $view = new Monitoring\Web\View();
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Unable to convert to JSON: missing state');
        $view->jsonSerialize();
    }

    /**
     * Checks `ms` of a view created after $before and completed before $after, with usleep(100000) in between.
     */
    protected function assertMeasuredTime(float $before, float $after, array $data): void
    {
        $this->assertGreaterThanOrEqual($before, $data['ms']['begin']);
        $this->assertGreaterThanOrEqual(0.1, $data['ms']['total']);
        $this->assertLessThanOrEqual($after - $before, $data['ms']['total']);
    }
}
