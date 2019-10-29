<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring;

/**
 * Class Exception
 * @package Horat1us\Yii\Monitoring
 */
class Exception extends \Exception
{
    /** @var array|null */
    protected $details = null;

    public function __construct(
        string $message,
        int $code,
        array $details = null,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->details = $details;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function __toString(): string
    {
        $string = parent::__toString() . PHP_EOL
            . "Details:" . PHP_EOL
            . json_encode($this->details) . PHP_EOL;

        return $string;
    }
}
