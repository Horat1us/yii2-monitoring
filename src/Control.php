<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring;

use yii\base;

/**
 * Class Control
 * @package Horat1us\Yii\Monitoring
 */
abstract class Control extends base\BaseObject implements ControlInterface
{
    /**
     * @throws Exception
     */
    protected function fail(string $message, int $code, array $details = []): void
    {
        throw new Exception(
            $message,
            $code,
            $details + $this->details()
        );
    }

    /**
     * @throws Exception
     */
    protected function assertEquals($expected, $actual, string $message, int $code, array $details = []): void
    {
        if ($expected === $actual) {
            return;
        }

        $this->fail($message, $code, $details + compact('expected', 'actual'));
    }

    protected function details(): array
    {
        return [];
    }
}
