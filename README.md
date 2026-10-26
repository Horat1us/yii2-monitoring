# Yii2 Monitoring
[![PHP Composer](https://github.com/Horat1us/yii2-monitoring/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/Horat1us/yii2-monitoring/actions/workflows/php.yml)
[![codecov](https://codecov.io/gh/horat1us/yii2-monitoring/branch/master/graph/badge.svg)](https://codecov.io/gh/horat1us/yii2-monitoring)

Package allows to define custom monitoring controls (rules) and execute them using web endpoints or console methods.  
Includes Web API and CLI controllers.  

## Installation
```bash
composer require horat1us/yii2-monitoring:^1.6
```

Requires PHP >= 8.4 and `yiisoft/yii2` ^2.0.54.  
Compatibility: tested on PHP 8.4, PHP 8.5.

## Documentation
- [Apiary](https://yii2monitoring.docs.apiary.io/#)
- [Blueprint API](./apiary.apib)
- [Configuration](./docs/CONFIGURE.md) - integrate monitoring to your Yii2 application
- [Usage](./docs/USAGE.md) - using of built-in controls

## Structure
- [Source Code](./src)
    - [Control](./src/Control) - out-of-box implemented controls
    - [Web](./src/Web) - handling web requests and forming responses
    - [Console](./src/Console) - handles console interaction   
    - [Exception](./src/Exception.php) - exception caught by [controls](./src/ControlInterface.php)
    - [Control Interface](./src/ControlInterface.php) - monitoring rule interface
    - [Control](./src/Control.php) - monitoring rule abstract implementation using yii BaseObject
- [Tests](./tests)
    - [Unit](./tests/Unit) - all PhpUnit test cases

## Suggest
- [wearesho-team/yii2-monitoring-fs](https://github.com/wearesho-team/yii2-monitoring-fs) - Yii2 filesystem monitoring

## Contributors
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)

## License
[MIT](./LICENSE)
