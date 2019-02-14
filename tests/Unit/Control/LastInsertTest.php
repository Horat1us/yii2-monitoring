<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control;

use Horat1us\Yii\Monitoring\Control\LastInsert;
use PHPUnit\Framework\TestCase;
use yii\db\Query;

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
                return $this->createMock(Query::class);
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
                return $this->createMock(Query::class);
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
                return $this->createMock(Query::class);
            },
            'interval' => false
        ]);
    }
}
