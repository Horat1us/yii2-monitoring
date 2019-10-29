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
- [Dependency](../src/Control/Dependency.php) - blocking control execution using dependencies
    - [Dependency\TimeLimit](../src/Control/Dependency/TimeLimit.php) - control dependency to block execution due to current time
    - [Dependency\Item](../src/Control/Dependency/Item.php) - control dependency to block execution using
- [TimeLimited](../src/Control/TimeLimited.php) - *deprecated*, blocking control execution using proxy-pattern.  

This example will check for last record (in order table) in database between 09:00 and 22:00.    
```php
<?php

use Horat1us\Yii\Monitoring;
use yii\db;

class Order extends db\ActiveRecord {
    // ...
}

$controller = [
    'class' => Monitoring\Web\Controller::class,
    'controls' => [
        'orders' => [
            'class' => Monitoring\Control\Dependency::class,
            'dependencies' => [
                [
                    'class' => Monitoring\Control\Dependency\TimeLimit::class,
                    'min' => '09:00:00',
                    'max' => '22:00:00',     
                ],    
            ],
            'control' => [
                'class' => Monitoring\Control\LastInsert::class,    
                'query' => [Order::class, 'find'], // \Closure or callable, return db\Query
                'attribute' => 'created_at', // default
                'format' => 'Y-m-d H:i:s', // default
            ],
        ],    
    ],
];
```
*Note: php timezone will be used*

## Console (CLI)
You can also may perform health-check using Yii2 CLI.  

### Configuration
```php
<?php
// console/main.php application configuration

return [
    // ... other attributes
    'controllerMap' => [
        // ... other controllers
        'health-check' => [
            'class' => Horat1us\Yii\Monitoring\Console\Controller::class,
            'controls' => [
                'db' => Horat1us\Yii\Monitoring\Control\Database::class,
                'cache' => Horat1us\Yii\Monitoring\Control\Cache::class,
                'queue' => Horat1us\Yii\Monitoring\Control\Queue::class,
            ],
        ],
    ],
];
```

### Usage

1. Perform Batch Test (run all controls)
```bash
php yii health-check --format=json
```
Output:
```json
{
 "state": "ok",
 "ms": {
  "begin": 1572383083.072824,
  "total": 1.0197219848632812
 },
 "details": {
  "db": {
   "state": "ok",
   "ms": {
    "begin": 1572383083.072836,
    "total": 0.007030963897705078
   }
  },
  "cache": {
   "state": "ok",
   "ms": {
    "begin": 1572383083.079887,
    "total": 0.00561213493347168
   },
   "details": {
    "type": "yii\\redis\\Cache"
   }
  },
  "queue": {
   "state": "ok",
   "ms": {
    "begin": 1572383083.085517,
    "total": 1.0069940090179443
   },
   "details": {
    "type": "yii\\queue\\redis\\Queue"
   }
  }
 }
}
```
2. Perform single control test
```bash
php yii health-check/execute db --format=json
```
Output:
```json
{
 "state": "ok",
 "ms": {
  "begin": 1572383386.694304,
  "total": 0.006947040557861328
 }
}
```
