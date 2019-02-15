<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Control\Dependency;

use yii\base;

/**
 * Class Item
 * @package Horat1us\Yii\Monitoring\Control\Dependency
 */
class Item extends base\BaseObject implements ItemInterface
{
    /** @var \Closure|callable */
    public $match;

    /**
     * @return array
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if (!is_callable($this->match)) {
            throw new base\InvalidConfigException("Match callback have to be callable", 1);
        }
        $this->match = \Closure::fromCallable($this->match);
    }

    public function match(): bool
    {
        return (bool)call_user_func($this->match);
    }
}
