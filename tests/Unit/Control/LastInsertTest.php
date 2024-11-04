<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control;

use DateInterval;
use Horat1us\Yii\Monitoring\Control\LastInsert;
use PHPUnit\Framework\TestCase;
use yii\base;
use yii\db;

class LastInsertTest extends TestCase
{
    public function testInitWithInvalidQuery(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Query have to be specified as \Closure or callable array');
        $this->expectExceptionCode(1);

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

    public function testInvalidInterval(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid interval specification');
        $this->expectExceptionCode(2);

        new LastInsert([
            'query' => function () {
                return $this->createMock(db\Query::class);
            },
            'interval' => 'Invalid 123'
        ]);
    }

    public function testIncorrectTypeOFIntervalAttribute(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Interval have to be specified as \DateInterval or interval specification');

        new LastInsert([
            'query' => function () {
                return $this->createMock(db\Query::class);
            },
            'interval' => false
        ]);
    }


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

        $this->expectException(\Horat1us\Yii\Monitoring\Exception::class);
        $this->expectExceptionMessage('Последняя запись не найдена');
        $this->expectExceptionCode(401);

        $lastInsert->execute();
    }


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

        $this->expectException(\Horat1us\Yii\Monitoring\Exception::class);
        $this->expectExceptionMessage('Ошибка получения времени записи');
        $this->expectExceptionCode(402);

        $lastInsert->execute();
    }

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

        $this->expectException(\Horat1us\Yii\Monitoring\Exception::class);
        $this->expectExceptionMessageMatches(
            '/Последняя запись от '
            . '[0-9]{4}-(1[0-2]{1}|0[1-9]{1})-'
            . '(0[0-9]{1}|[0-1]{1}[0-9]{1}|3[0-1]{1})T([0-1]{1}[0-9]{1}|2[0-4]{1}):[0-5]{1}/'
        );
        $this->expectExceptionCode(403);

        $lastInsert->execute();
    }

    public function testTest(): void
    {
        $interval = 'P2Y4DT6H8M';
        $lastInsert = new LastInsert([
            'query' => function () use ($interval) {
                $mock = $this->createMock(db\Query::class);
                $mock->expects($this->once())
                    ->method('max')
                    ->with('created_at')
                    ->willReturn(
                        (new \DateTime())->sub(new \DateInterval($interval))->add(new \DateInterval("PT6H"))->format(
                            'Y-m-d'
                        )
                    );
                return $mock;
            },
            'interval' => $interval,
            'format' => 'Y-m-d'
        ]);

        $result = $lastInsert->execute();
        $this->assertInstanceOf(\DateTime::class, new \DateTime($result['expected']));
        $this->assertInstanceOf(\DateTime::class, new \DateTime($result['actual']));
    }
}
