<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control\Dependency;

use PHPUnit\Framework\TestCase;
use Horat1us\Yii\Monitoring;

/**
 * Class ItemTest
 * @package Horat1us\Yii\Monitoring\Tests\Unit\Control\Dependency
 */
class ItemTest extends TestCase
{
    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionCode 1
     * @expectedExceptionMessage Match callback have to be callable
     */
    public function testInvalidMatch(): void
    {
        new Monitoring\Control\Dependency\Item(['match' => new \stdClass]);
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
