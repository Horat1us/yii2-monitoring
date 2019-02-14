<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Control;

use yii\di;
use yii\base;
use Horat1us\Yii\Monitoring;

/**
 * Class TimeLimited
 * @package Horat1us\Yii\Monitoring\Control
 * @deprecated Use Dependency with Dependency\TimeLimit item
 * @see Dependency
 * @see Monitoring\Control\Dependency\TimeLimit
 */
class TimeLimited extends Dependency
{
    /**
     * Minimal time to execute control
     * Format H:i:s
     * @var string
     */
    public $min;

    /**
     * Maximal time to execute control
     * Format H:i:s
     * @var string
     */
    public $max;

    /**
     * Control to be executed in specified time range
     * @var string|array|Monitoring\ControlInterface
     */
    public $control;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        $this->dependencies = [
            [
                'class' => Dependency\TimeLimit::class,
                'min' => $this->min,
                'max' => $this->max,
            ],
        ];
        parent::init();
    }
}
