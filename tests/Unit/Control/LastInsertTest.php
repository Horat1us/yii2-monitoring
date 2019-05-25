<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control;

use Horat1us\Yii\Monitoring\Control\LastInsert;
use PHPUnit\Framework\TestCase;
use yii\db;

/**
 * Class LastInsertTest
 * @package Horat1us\Yii\Monitoring\Tests\Unit\Control
 */
class LastInsertTest extends TestCase
{
    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Query have to be specified as \Closure or callable array
     * @expectedExceptionCode 1
     */
    public function testInitWithInvalidQuery(): void
    {
        new LastInsert([
            'query' => false
        ]);
    }

    public function testValidInterval(): void
    {
        $lastInsert = new LastInsert([
            'query' => function () {
                return $this->createMock(db\Query::class);
            },
            'interval' => 'PT3M'
        ]);

        $this->assertEquals(new \DateInterval('PT3M'), $lastInsert->interval);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Invalid interval specification
     * @expectedExceptionCode 2
     */
    public function testInvalidInterval(): void
    {
        new LastInsert([
            'query' => function () {
                return $this->createMock(db\Query::class);
            },
            'interval' => 'Invalid 123'
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Interval have to be specified as \DateInterval or interval specification
     */
    public function testIncorrectTypeOFIntervalAttribute(): void
    {
        new LastInsert([
            'query' => function () {
                return $this->createMock(db\Query::class);
            },
            'interval' => false
        ]);
    }

    /**
     * @expectedException \Horat1us\Yii\Monitoring\Exception
     * @expectedExceptionMessage Последняя запись не найдена
     * @expectedExceptionCode 401
     */
    public function testFailedScalar(): void
    {
        $lastInsert = new LastInsert([
            'query' => function () {
                $mock = $this->createMock(db\Query::class);
                $mock->expects($this->once())
                    ->method('max')
                    ->with('"table"."column"')
                    ->willReturn(null);
                return $mock;
            },
            'interval' => 'PT3M'
        ]);

        $lastInsert->attribute = '"table"."column"';
        $lastInsert->execute();
    }

    /**
     * @expectedException \Horat1us\Yii\Monitoring\Exception
     * @expectedExceptionMessage Ошибка получения времени записи
     * @expectedExceptionCode 402
     */
    public function testFailedDateTime(): void
    {
        $lastInsert = new LastInsert([
            'query' => function () {
                $mock = $this->createMock(db\Query::class);
                $mock->expects($this->once())
                    ->method('max')
                    ->with('created_at')
                    ->willReturn('invalidDate');
                return $mock;
            },
            'interval' => 'PT3M',
            'format' => 'Y-m-d'
        ]);

        $lastInsert->execute();
    }

    /**
     * @expectedException \Horat1us\Yii\Monitoring\Exception
     * @expectedExceptionMessageRegExp /Последняя запись от [0-9]{4}-(1[0-2]{1}|0[1-9]{1})-(0[0-9]{1}|[0-1]{1}[0-9]{1}|3[0-1]{1})T([0-1]{1}[0-9]{1}|2[0-4]{1}):[0-5]{1}/
     * @expectedExceptionCode 403
     */
    public function testFailedAwait(): void
    {
        $lastInsert = new LastInsert([
            'query' => function () {
                $mock = $this->createMock(db\Query::class);
                $mock->expects($this->once())
                    ->method('max')
                    ->with('created_at')
                    ->willReturn('2018-03-12');
                return $mock;
            },
            'interval' => 'PT3M',
            'format' => 'Y-m-d'
        ]);

        $lastInsert->execute();
    }

    public function testTest(): void
    {
        $lastInsert = new LastInsert([
            'query' => function () {
                $mock = $this->createMock(db\Query::class);
                $mock->expects($this->once())
                    ->method('max')
                    ->with('created_at')
                    ->willReturn('2018-03-12');
                return $mock;
            },
            'interval' => 'P2Y4DT6H8M',
            'format' => 'Y-m-d'
        ]);

        $result = $lastInsert->execute();
        $this->assertInstanceOf(\DateTime::class, new \DateTime($result['expected']));
        $this->assertInstanceOf(\DateTime::class, new \DateTime($result['actual']));
    }
}
