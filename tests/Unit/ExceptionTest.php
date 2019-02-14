<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Tests\Unit;

use Horat1us\Yii\Monitoring;
use PHPUnit\Framework\TestCase;

/**
 * Class ExceptionTest
 * @package Horat1us\Yii\Monitoring\Tests\Unit
 */
class ExceptionTest extends TestCase
{
    protected const MESSAGE = 'Message';
    protected const CODE = -1;
    protected const DETAILS = [
        'key' => 'value',
    ];

    /** @var Monitoring\Exception */
    protected $exception;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exception = new Monitoring\Exception(
            static::MESSAGE,
            static::CODE,
            static::DETAILS
        );
    }

    public function getGetMessage(): void
    {
        $this->assertEquals(static::MESSAGE, $this->exception->getMessage());
    }

    public function testGetCode(): void
    {
        $this->assertEquals(static::CODE, $this->exception->getCode());
    }

    public function testGetDetails(): void
    {
        $this->assertEquals(static::DETAILS, $this->exception->getDetails());
    }
}
