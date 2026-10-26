<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control;

use Horat1us\Yii\Monitoring\Control;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use yii\base;

class TimeLimitedTest extends TestCase
{
    use PHPMock;

    protected const NAMESPACE = 'Horat1us\\Yii\\Monitoring\\Control\\Dependency';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // Define the mock before TimeLimit::match() first calls date(), otherwise PHP keeps the global function
        static::defineFunctionMock(static::NAMESPACE, 'date');
    }

    public function testInvalidConfig(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Minimal or maximal time limit have to be specified as string');
        $this->expectExceptionCode(1);
        new Control\TimeLimited([
            'control' => $this->createStub(Control\Cache::class)
        ]);
    }

    public function testInvalidMinTime(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid time format: invalid');
        $this->expectExceptionCode(2);
        new Control\TimeLimited([
            'control' => $this->createStub(Control\Cache::class),
            'min' => 'invalid'
        ]);
    }

    public function testInvalidMaxTime(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid time format: invalid');
        $this->expectExceptionCode(2);
        new Control\TimeLimited([
            'control' => $this->createStub(Control\Cache::class),
            'max' => 'invalid'
        ]);
    }

    public function testSuccess(): void
    {
        $this->mockCurrentTime('12:00:00');
        $control = $this->createMock(Control\Cache::class);
        $control->expects($this->once())
            ->method('execute')
            ->willReturn(['test']);
        $timeLimited = new Control\TimeLimited([
            'control' => $control,
            'min' => '00:00:00',
            'max' => '23:59:59',
        ]);

        $this->assertEquals(['test'], $timeLimited->execute());
    }

    public function testMin(): void
    {
        $this->mockCurrentTime('12:00:00');
        $control = $this->createMock(Control\Cache::class);
        $control->expects($this->never())->method('execute');
        $timeLimited = new Control\TimeLimited([
            'control' => $control,
            'min' => '12:00:01',
        ]);

        $this->assertEquals(
            [
                'type' => get_class($control),
                'executed' => false,
                'dependency' => Control\Dependency\TimeLimit::class,
                'min' => '12:00:01',
                'max' => null,
            ],
            $timeLimited->execute()
        );
    }

    public function testMax(): void
    {
        $this->mockCurrentTime('12:00:00');
        $control = $this->createMock(Control\Cache::class);
        $control->expects($this->never())->method('execute');
        $timeLimited = new Control\TimeLimited([
            'control' => $control,
            'max' => '11:59:59',
        ]);

        $this->assertEquals(
            [
                'type' => get_class($control),
                'executed' => false,
                'dependency' => Control\Dependency\TimeLimit::class,
                'min' => null,
                'max' => '11:59:59',
            ],
            $timeLimited->execute()
        );
    }

    protected function mockCurrentTime(string $time): void
    {
        $date = $this->getFunctionMock(static::NAMESPACE, 'date');
        $date->expects($this->once())
            ->with('H:i:s')
            ->willReturn($time);
    }
}
