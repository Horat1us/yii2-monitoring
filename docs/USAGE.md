# Yii2 Monitoring Usage

To specify monitoring controls (rules) you have to use [ControlInterface](../src/ControlInterface.php).
There is some default controls implementations:

- [Cache](../src/Control/Cache.php) - testing Yii2 cache component (read/write)
- [Queue](../src/Control/Queue.php) - testing queue (pushing and executing tasks).
*Note: you need to install yiisoft/yii2-queue to use it first*
- [Database](../src/Control/Database.php) - testing database component via executing query
and comparing result to expected.
- [LastInsert](../src/Control/LastInsert.php) - fetches last record in database using query from configuration.
It will compare date time attribute with specified interval.
- [TimeLimited](../src/Control/TimeLimited.php) - blocking control execution using proxy-pattern.

This example will check for last record (in order table) in database between 09:00 and 22:00.    
```php
<?php

use Horat1us\Yii\Monitoring;
use yii\db;

$controller = [
    'class' => Monitoring\Web\Controller::class,
    'controls' => [
        'orders' => [
            'class' => Monitoring\Control\TimeLimited::class,
            'min' => '09:00:00',
            'max' => '22:00:00',
            'control' => [
                'class' => Monitoring\Control\LastInsert::class,    
                'query' => function(): db\Query {
                    // Order - db\ActiveRecord class for `order` table
                    return Order::find();
                },
                'attribute' => 'created_at', // default
                'format' => 'Y-m-d H:i:s', // default
            ],
        ],    
    ],
];
```
*Note: php timezone will be used*
