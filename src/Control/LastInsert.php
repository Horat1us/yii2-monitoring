<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Control;

use yii\db;
use yii\base;
use Horat1us\Yii\Monitoring;

/**
 * Class LastInsert
 * @package Horat1us\Yii\Monitoring\Control
 */
class LastInsert extends Monitoring\Control
{
    public const CODE_NOT_FOUND = 401;
    public const CODE_INVALID_FORMAT = 402;
    public const CODE_INTERVAL_REACHED = 403;

    /**
     * Closure that returns Query to check
     *
     * @var \Closure|array
     */
    public $query;

    /**
     * \DateInterval instance or interval specification
     * @see \DateInterval::__construct
     *
     * @var \DateInterval|string
     */
    public $interval;

    /**
     * Attribute to fetch insert time
     *
     * @var string
     */
    public $attribute = 'created_at';

    /**
     * Date time format to parse attribute
     *
     * @see \DateTime::createFromFormat
     * @link http://php.net/manual/ru/datetime.createfromformat.php
     *
     * @var string
     */
    public $format = 'Y-m-d H:i:s';

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $isValidQuery = $this->query instanceof \Closure
            || is_callable($this->query) && is_array($this->query);

        if (!$isValidQuery) {
            throw new base\InvalidConfigException(
                "Query have to be specified as \\Closure or callable array",
                1
            );
        }
        if (is_string($this->interval)) {
            try {
                $this->interval = new \DateInterval($this->interval);
            } catch (\Exception $e) {
                throw new base\InvalidConfigException(
                    "Invalid interval specification",
                    2,
                    $e
                );
            }
        } elseif (!$this->interval instanceof \DateInterval) {
            throw new base\InvalidConfigException(
                "Interval have to be specified as \\DateInterval or interval specification"
            );
        }
    }

    /**
     * @throws \Throwable
     * @throws Monitoring\Exception
     */
    public function execute(): ?array
    {
        /** @var db\Query $query */
        $query = call_user_func($this->query);

        $value = $query
            ->max($this->attribute);

        $this->assertEquals(
            true,
            is_string($value),
            "Последняя запись не найдена",
            static::CODE_NOT_FOUND
        );

        $insertDateTime = \DateTime::createFromFormat($this->format, $value);
        $this->assertEquals(
            true,
            $insertDateTime instanceof \DateTime,
            "Ошибка получения времени записи",
            static::CODE_INVALID_FORMAT
        );

        $expectedDateTime = date_create()->sub($this->interval);

        $actual = $insertDateTime->format(DATE_RFC3339);
        $expected = $expectedDateTime->format(DATE_RFC3339);

        if ($expectedDateTime > $insertDateTime) {
            $this->fail(
                "Последняя запись от {$actual} при ожидании до {$expected}",
                static::CODE_INTERVAL_REACHED,
                compact('actual', 'expected')
            );
        }

        return compact('expected', 'actual');
    }
}
