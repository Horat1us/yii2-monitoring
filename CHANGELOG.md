# Change Log

## 1.6.0 - [Unreleased]
- Require PHP >= 8.4 (was ^7.2 | ^8.0); tested on PHP 8.4 and PHP 8.5
- Require `yiisoft/yii2` ^2.0.54 (was ^2.0.15): first release with PHP 8.5 fixes
- Fix PHP 8.4 implicitly nullable parameter deprecations in
  [Exception::__construct()](./src/Exception.php) and [Web\View::success()](./src/Web/View.php)
- Upgrade dev tooling: PHPUnit 13, PHP_CodeSniffer 4; CI runs on PHP 8.4 and 8.5

## 1.4.0 - [30.09.2019]
- Implement [console controller](./src/Console/Controller.php)
- Swap web\Controller error codes constants names

## 1.3.0 - [25.05.2019]
- LastInsert control database query optimization

## 1.0.0-RC1 - Implementation [14.02.2019]
- Control Interface
- Exception
- Abstract control implementation
- Built-in controls: cache, database, last insert, queue, time-limited wrapper
- Web controller and view
