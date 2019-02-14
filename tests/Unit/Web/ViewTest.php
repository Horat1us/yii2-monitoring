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
        $view = new Monitoring\Web\View();
        sleep(1);

        $details = ['someDetails'];
        $clonedView = $view->success($details);
        $clonedViewData = $clonedView->jsonSerialize();

        $this->assertNotSame($clonedView, $view);
        $this->assertGreaterThan($clonedViewData['ms']['total'], $clonedViewData['ms']['begin']);
        $this->assertEquals(Monitoring\Web\View::STATE_OK, $clonedViewData['state']);
        $this->assertEquals($details, $clonedViewData['details']);
    }

    public function testFail(): void
    {
        $view = new Monitoring\Web\View();
        sleep(1);

        $exception = new \RuntimeException('Some exception');
        $clonedView = $view->fail($exception);
        $clonedViewData = $clonedView->jsonSerialize();

        $this->assertNotSame($clonedView, $view);

        $this->assertGreaterThan($clonedViewData['ms']['total'], $clonedViewData['ms']['begin']);
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
        $view = new Monitoring\Web\View();
        sleep(1);

        $details = ['someDetails'];
        $exception = new Monitoring\Exception('Some exception', -1, $details);
        $clonedView = $view->fail($exception);
        $clonedViewData = $clonedView->jsonSerialize();

        $this->assertNotSame($clonedView, $view);

        $this->assertGreaterThan($clonedViewData['ms']['total'], $clonedViewData['ms']['begin']);
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
}
