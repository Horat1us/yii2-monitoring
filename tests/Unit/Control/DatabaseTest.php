<?php

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control;

use Horat1us\Yii\Monitoring\Control\Database;
use Horat1us\Yii\Monitoring\Exception;
use PHPUnit\Framework\TestCase;
use yii\db;

/**
 * Class DatabaseTest
 * @package Horat1us\Yii\Monitoring\Tests\Unit\Control
 */
class DatabaseTest extends TestCase
{
    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Query must be specified as string, \Closure or \yii\db\Query
     * @expectedExceptionCode 1
     */
    public function testFailedInitWithInvalidQuery(): void
    {
        new Database([
            'db' => $this->createMock(db\Connection::class),
            'query' => false
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Assert must be specified as string or \Closure
     * @expectedExceptionCode 2
     */
    public function testFailedInitWithInvalidAssert(): void
    {
        new Database([
            'db' => $this->createMock(db\Connection::class),
            'assert' => false
        ]);
    }

    public function testClosureQuery(): void
    {
        $database = new Database([
            'db' => $this->createMock(db\Connection::class),
            'query' => function () {
                return 'select 1';
            }
        ]);

        $this->assertEquals('select 1', $database->query);
    }

    public function testSuccess(): void
    {
        $command = $this->createMock(db\Command::class);
        $command->expects($this->once())
            ->method('queryScalar')
            ->willReturn('result');
        $db = $this->createMock(db\Connection::class);
        $db->expects($this->once())
            ->method('createCommand')
            ->with($this->equalTo('SELECT 1;'))
            ->willReturn($command);

        $database = new Database([
            'db' => $db,
            'assert' => 'result'
        ]);

        $this->assertNull($database->execute());
    }

    public function testExceptionQueryScalar(): void
    {
        $this->expectExceptionObject(new Exception(
            "Ошибка выполнения запроса",
            Database::CODE_QUERY,
            ['query' => 'test']
        ));
        $command = $this->createMock(db\Command::class);
        $command->expects($this->once())
            ->method('queryScalar')
            ->willThrowException(new db\Exception('Some exception'));
        $db = $this->createMock(db\Connection::class);
        $db->expects($this->once())
            ->method('createCommand')
            ->with($this->equalTo('SELECT 1;'))
            ->willReturn($command);

        $database = new Database([
            'db' => $db,
        ]);

        $database->execute();
    }

    public function testFailedAssertScalar(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Database::CODE_ASSERT);
        $this->expectExceptionMessage("Неверный ответ запроса");
        $command = $this->createMock(db\Command::class);
        $command->expects($this->once())
            ->method('queryScalar')
            ->willReturn('invalid');
        $db = $this->createMock(db\Connection::class);
        $db->expects($this->once())
            ->method('createCommand')
            ->with($this->equalTo('SELECT 1;'))
            ->willReturn($command);

        $database = new Database([
            'db' => $db,
            'assert' => 'result'
        ]);

        $this->assertNull($database->execute());
    }

    public function testFailedAssertWithClosure(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Database::CODE_ASSERT);
        $this->expectExceptionMessage("Ошибка сравнения результата запроса");
        $command = $this->createMock(db\Command::class);
        $command->expects($this->once())
            ->method('queryScalar')
            ->willReturn('invalid');
        $db = $this->createMock(db\Connection::class);
        $db->expects($this->once())
            ->method('createCommand')
            ->with($this->equalTo('SELECT 1;'))
            ->willReturn($command);

        $database = new Database([
            'db' => $db,
            'assert' => function ($left) {
                return false;
            }
        ]);

        $this->assertNull($database->execute());
    }

    public function testExecuteWithQueryClass(): void
    {
        $command = $this->createMock(db\Command::class);
        $command->expects($this->once())
            ->method('queryScalar')
            ->willReturn('result');
        $query = $this->createMock(db\Query::class);
        $query->expects($this->once())
            ->method('createCommand')
            ->willReturn($command);
        $db = $this->createMock(db\Connection::class);

        $database = new Database([
            'db' => $db,
            'query' => $query,
            'assert' => 'result'
        ]);

        $this->assertNull($database->execute());
    }
}
