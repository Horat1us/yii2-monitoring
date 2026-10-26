# Change Log

## 1.6.0 - [26.10.2026]
- Require PHP >= 8.4 (was ^7.2 | ^8.0); tested on PHP 8.4 and PHP 8.5
- Require `yiisoft/yii2` ^2.0.54 (was ^2.0.15): first release with PHP 8.5 fixes
- Fix PHP 8.4 implicitly nullable parameter deprecations in
  [Exception::__construct()](./src/Exception.php) and [Web\View::success()](./src/Web/View.php)
- Fix [console controller](./src/Console/Controller.php) failure output with `--format=json`:
  only the JSON is printed, without the error text on stderr (the 1.5.1 fix, missing from 1.5.2)
- [Database control](./src/Control/Database.php) query error (code 501) keeps the database exception
  as the previous exception, so logs show why the query failed
- Upgrade dev tooling: PHPUnit 13, PHP_CodeSniffer 4; CI runs on PHP 8.4 and 8.5
- Test the console controller; fix tests that never ran, could not fail or depended on the time of day

## 1.5.2 - [04.11.2024]
- Allow PHP 8 (`^7.2 | ^8.0`)
- Upgrade dev tooling: PHPUnit 9, PSR-12 code style; CI moved from Travis CI to GitHub Actions
- Tagged from `master`, which did not include the 1.5.1 fix (restored in 1.6.0)

## 1.5.1 - [06.11.2019]
- Fix console controller failure output with `--format=json`: only the JSON is printed

## 1.5.0 - [29.10.2019]
- Console controller `--format` (`-f`) option: `raw` (default) or `json`
- [Exception](./src/Exception.php) string representation includes details

## 1.4.1 - [30.09.2019]
- Fix console controller output: line break after the error, pretty-printed details

## 1.4.0 - [30.09.2019]
- Implement [console controller](./src/Console/Controller.php)
- Swap web\Controller error codes constants names

## 1.3.1 - [05.09.2019]
- Web controller logs the exception object instead of its string representation

## 1.3.0 - [25.05.2019]
- LastInsert control database query optimization

## 1.2.1 - [21.02.2019]
- Suggest `wearesho-team/yii2-monitoring-fs`

## 1.2.0 - [15.02.2019]
- [Dependency control](./src/Control/Dependency.php): executes a control only when all dependency items match.
  Items: [callable](./src/Control/Dependency/Item.php), [time limit](./src/Control/Dependency/TimeLimit.php)
- [TimeLimited control](./src/Control/TimeLimited.php) is deprecated in favour of Dependency with a time limit item

## 1.1.0 - [15.02.2019]
- Database control: scalar `assert` (was string only), default `1`

## 1.0.2 - [15.02.2019]
- Database control: `db` defaults to the `db` component

## 1.0.1 - [14.02.2019]
- Fix docs

## 1.0.0 - [14.02.2019]
- Require `ext-json`
- Unit tests for all controls, controller and view

## 1.0.0-RC1 - Implementation [14.02.2019]
- Control Interface
- Exception
- Abstract control implementation
- Built-in controls: cache, database, last insert, queue, time-limited wrapper
- Web controller and view
