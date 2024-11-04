<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control;

use Horat1us\Yii\Monitoring\Control;
use PHPUnit\Framework\TestCase;
use yii\base;

class TimeLimitedTest extends TestCase
{
    public function testInvalidConfig(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Minimal or maximal time limit have to be specified as string');
        $this->expectExceptionCode(1);
        new Control\TimeLimited([
            'control' => $this->createMock(Control\Cache::class)
        ]);
    }

    public function testInvalidMinTime(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid time format: invalid');
        $this->expectExceptionCode(2);
        new Control\TimeLimited([
            'control' => $this->createMock(Control\Cache::class),
            'min' => 'invalid'
        ]);
    }

    public function testInvalidMaxTime(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid time format: invalid');
        $this->expectExceptionCode(2);
        new Control\TimeLimited([
            'control' => $this->createMock(Control\Cache::class),
            'max' => 'invalid'
        ]);
    }

    public function testSuccess(): void
    {
        $control = $this->createMock(Control\Cache::class);
        $control->expects($this->once())
            ->method('execute')
            ->willReturn(['test']);
        $timeLimited = new Control\TimeLimited([
            'control' => $control,
            'min' => date('00:00:00'),
            'max' => date('23:59:59')
        ]);

        $this->assertEquals(['test'], $timeLimited->execute());
    }

    public function testMin(): void
    {
        $control = $this->createMock(Control\Cache::class);
        $timeLimited = new Control\TimeLimited([
            'control' => $control,
            'min' => date('23:59:59'),
        ]);

        $result = $timeLimited->execute();
        $this->assertArrayHasKey('type', $result);
    }

    public function testMax(): void
    {
        $control = $this->createMock(Control\Cache::class);
        $timeLimited = new Control\TimeLimited([
            'control' => $control,
            'max' => date('00:00:00'),
        ]);

        $result = $timeLimited->execute();
        $this->assertArrayHasKey('type', $result);
    }
}
