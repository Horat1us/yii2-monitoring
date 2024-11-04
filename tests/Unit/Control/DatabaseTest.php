<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Tests\Unit\Control;

use Horat1us\Yii\Monitoring\Control\Database;
use Horat1us\Yii\Monitoring\Exception;
use PHPUnit\Framework\TestCase;
use yii\base;
use yii\db;

class DatabaseTest extends TestCase
{
    public function testFailedInitWithInvalidQuery(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Query must be specified as string, \Closure or \yii\db\Query');
        $this->expectExceptionCode(1);

        new Database([
            'db' => $this->createMock(db\Connection::class),
            'query' => false
        ]);
    }

    public function testFailedInitWithInvalidAssert(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Assert must be specified as scalar or \Closure');
        $this->expectExceptionCode(2);

        new Database([
            'db' => $this->createMock(db\Connection::class),
            'assert' => ['not-scalar',],
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
