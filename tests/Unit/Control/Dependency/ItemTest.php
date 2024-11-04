<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control\Dependency;

use PHPUnit\Framework\TestCase;
use Horat1us\Yii\Monitoring;
use yii\base;

class ItemTest extends TestCase
{
    public function testInvalidMatch(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('Match callback have to be callable');
        new Monitoring\Control\Dependency\Item(['match' => new \stdClass()]);
    }

    public function testMatch(): void
    {
        $match = function (): bool {
            return true;
        };
        $fail = function (): bool {
            return false;
        };

        $item = new Monitoring\Control\Dependency\Item([
            'match' => $match,
        ]);
        $this->assertTrue($item->match());

        $item->match = $fail;
        $this->assertFalse($item->match());
    }
}
