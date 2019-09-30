<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Web;

use Horat1us\Yii\Monitoring;

/**
 * Class View
 * @package Horat1us\Yii\Monitoring\Web
 * #todo should be moved out of web folder when controller trait will be refactored into service.
 */
class View implements \JsonSerializable
{
    public const STATE_ERROR = 'error';
    public const STATE_OK = 'ok';
    public const STATE_PENDING = 'pending';

    /**
     * Monitoring execution begin microtime
     * @var float
     */
    protected $begin;

    /**
     * Monitoring execution complete microtime
     * @var float
     */
    protected $end;

    /**
     * See STATE_ constants
     * @var string
     */
    protected $state = self::STATE_PENDING;

    /**
     * Only for STATE_OK
     * @var array|null
     */
    protected $details;

    /**
     * Only for STATE_ERROR
     * @var \Throwable|null
     */
    protected $exception;

    public function __construct()
    {
        $this->begin = microtime(true);
    }

    public function success(array $details = null): View
    {
        $this->end = microtime(true);
        $this->state = static::STATE_OK;
        $this->details = $details;
        $this->exception = null;

        return clone $this;
    }

    public function fail(\Throwable $exception): View
    {
        $this->end = microtime(true);
        $this->state = static::STATE_ERROR;
        $this->exception = $exception;
        $this->details = null;

        return clone $this;
    }

    public function jsonSerialize(): array
    {
        if ($this->state === self::STATE_PENDING) {
            throw new \BadMethodCallException(
                "Unable to convert to JSON: missing state"
            );
        }

        $json = [
            'state' => $this->state,
            'ms' => [
                'begin' => $this->begin,
                'total' => $this->end - $this->begin,
            ],
        ];

        if ($this->exception instanceof \Throwable) {
            $error = [
                'type' => get_class($this->exception),
                'code' => $this->exception->getCode(),
                'message' => $this->exception->getMessage(),
            ];
            if ($this->exception instanceof Monitoring\Exception) {
                if (!is_null($details = $this->exception->getDetails())) {
                    $error['details'] = $details;
                }
            }
            $json['error'] = $error;
        }

        if (!is_null($this->details)) {
            $json['details'] = $this->details;
        }

        return $json;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getTotal(): ?float
    {
        return ($this->end ?? microtime(true)) - $this->begin;
    }

    public function getException(): ?\Throwable
    {
        return $this->exception;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }
}
