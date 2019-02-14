<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Control;

use yii\di;
use yii\base;
use Horat1us\Yii\Monitoring;

/**
 * Class TimeLimited
 * @package Horat1us\Yii\Monitoring\Control
 */
class TimeLimited extends Monitoring\Control
{
    protected const TIME_REGEX = '/^(([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9]))$/';

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
        parent::init();
        $this->control = di\Instance::ensure($this->control, Monitoring\ControlInterface::class);
        if (is_string($this->min)) {
            $this->validateTime($this->min);
        }
        if (is_string($this->max)) {
            $this->validateTime($this->max);
        } elseif (!is_string($this->min)) {
            throw new base\InvalidConfigException(
                "Minimal or maximal time limit have to be specified as string",
                1
            );
        }
    }

    public function execute(): ?array
    {
        if (!$this->isRangeMatch()) {
            return $this->details();
        }
        return $this->control->execute();
    }

    /**
     * @param string $value
     * @throws base\InvalidConfigException
     */
    protected function validateTime(string $value): void
    {
        if (preg_match(static::TIME_REGEX, $value) !== 1) {
            throw new base\InvalidConfigException(
                "Invalid time format: {$value}",
                2
            );
        }
    }

    protected function isRangeMatch(): bool
    {
        $current = date('H:i:s');
        return $this->min < $current && $current < $this->max;
    }

    protected function details(): array
    {
        return [
            'type' => get_class($this->control),
            'executed' => false,
            'min' => $this->min,
            'max' => $this->max,
        ];
    }
}
