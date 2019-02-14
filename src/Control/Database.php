<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Control;

use yii\db;
use yii\di;
use yii\base;
use Horat1us\Yii\Monitoring;

/**
 * Class Database
 * @package Horat1us\Yii\Monitoring\Control
 */
class Database extends Monitoring\Control
{
    public const CODE_QUERY = 501;
    public const CODE_ASSERT = 502;

    /** @var string|array|db\Connection */
    public $db = 'db';

    /**
     * Test query to execute in database
     *
     * \Closure - have to return db\Query
     *
     *
     * @var string|\Closure|db\Query
     */
    public $query = 'SELECT 1;';

    /**
     * Closure to validate query result (receiving scalar, returning boolean)
     * or scalar value to compare with result
     * It should return boolean
     *
     * @var string|int|\Closure
     */
    public $assert = 1;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->db = di\Instance::ensure($this->db, db\Connection::class);
        if ($this->query instanceof \Closure) {
            $this->query = call_user_func($this->query);
        }
        if (!is_string($this->query)
            && !$this->query instanceof db\Query
        ) {
            throw new base\InvalidConfigException(
                "Query must be specified as string, \Closure or \yii\db\Query",
                1
            );
        }

        if (!is_scalar($this->assert) && !$this->assert instanceof \Closure) {
            throw new base\InvalidConfigException(
                "Assert must be specified as scalar or \Closure",
                2
            );
        }
    }

    public function execute(): ?array
    {
        $command = $this->getCommand();

        try {
            $result = $command->queryScalar();
        } catch (db\Exception $exception) {
            throw new Monitoring\Exception(
                "Ошибка выполнения запроса",
                static::CODE_QUERY,
                [
                    'query' => $command->rawSql,
                ]
            );
        }

        if (is_scalar($this->assert)) {
            $this->assertEquals(
                $this->assert,
                $result,
                "Неверный ответ запроса",
                static::CODE_ASSERT
            );
        } else {
            call_user_func($this->assert, $result) || $this->fail(
                "Ошибка сравнения результата запроса",
                static::CODE_ASSERT,
                [
                    'actual' => $result,
                ]
            );
        }

        return null;
    }

    protected function getCommand(): db\Command
    {
        if (is_string($this->query)) {
            return $this->db->createCommand($this->query);
        }
        return $this->query->createCommand($this->db);
    }
}
