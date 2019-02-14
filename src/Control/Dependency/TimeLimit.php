<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Control\Dependency;

use yii\base;

/**
 * Class TimeLimit
 * @package Horat1us\Yii\Monitoring\Control\Dependency
 */
class TimeLimit extends base\BaseObject implements ItemInterface, \JsonSerializable
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
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
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

    public function match(): bool
    {
        $current = date('H:i:s');
        return (($this->min ?? '00:00:00') <= $current) && ($current <= ($this->max ?? '23:59:59'));
    }

    public function jsonSerialize(): array
    {
        return [
            'min' => $this->min,
            'max' => $this->max,
        ];
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
}
