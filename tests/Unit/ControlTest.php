<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit;

use Horat1us\Yii\Monitoring\Control;
use PHPUnit\Framework\TestCase;

/**
 * Class ControlTest
 * @package Horat1us\Yii\Monitoring\Tests\Unit
 */
class ControlTest extends TestCase
{
    public function testFailWithoutException(): void
    {
        $control = new class extends Control
        {
            public $message;

            public $code;

            protected function fail(string $message, int $code, array $details = []): void
            {
                $this->message = $message;
                $this->code = $code;
                return;
            }

            public function execute(): ?array
            {
                $this->assertEquals(1, 2, 'message', -1);

                return ['test'];
            }
        };

        $this->assertEquals(['test'], $control->execute());
        $this->assertEquals('message', $control->message);
        $this->assertEquals(-1, $control->code);
    }
}
