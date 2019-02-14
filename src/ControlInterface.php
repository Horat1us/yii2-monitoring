<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring;

use Horat1us\Yii\Monitoring;

/**
 * Interface ControlInterface
 * @package Horat1us\Yii\Monitoring
 */
interface ControlInterface
{
    /**
     * @throws \Throwable
     * @throws Monitoring\Exception
     */
    public function execute(): ?array;
}
