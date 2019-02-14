<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit\Web;

use Horat1us\Yii\Monitoring\Web\Controller;
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
        $controller = new Controller('id', $this->createMock(Module::class));
        $controller->actionControl(1);
    }
}
