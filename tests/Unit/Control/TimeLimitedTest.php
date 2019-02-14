<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control;

use Horat1us\Yii\Monitoring\Control;
use PHPUnit\Framework\TestCase;

/**
 * Class TimeLimitedTest
 * @package Horat1us\Yii\Monitoring\Tests\Unit\Control
 */
class TimeLimitedTest extends TestCase
{
    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Minimal or maximal time limit have to be specified as string
     * @expectedExceptionCode 1
     */
    public function testInvalidConfig(): void
    {
        new Control\TimeLimited([
            'control' => $this->createMock(Control\Cache::class)
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessageInvalid time format: invalid
     * @expectedExceptionCode 2
     */
    public function testInvalidMinTime(): void
    {
        new Control\TimeLimited([
            'control' => $this->createMock(Control\Cache::class),
            'min' => 'invalid'
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessageInvalid time format: invalid
     * @expectedExceptionCode 2
     */
    public function testInvalidMaxTime(): void
    {
        new Control\TimeLimited([
            'control' => $this->createMock(Control\Cache::class),
            'max' => 'invalid'
        ]);
    }
}
