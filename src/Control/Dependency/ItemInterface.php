<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Control\Dependency;

/**
 * Interface Item
 * @package Horat1us\Yii\Monitoring\Control\Dependency
 */
interface ItemInterface
{
    public function match(): bool;
}
