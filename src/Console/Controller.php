<?php

namespace Horat1us\Yii\Monitoring\Console;

use Horat1us\Yii\Monitoring;
use yii\helpers;
use yii\console;
use yii\base;

/**
 * Class Controller
 * @package Horat1us\Yii\Monitoring\Console
 */
class Controller extends console\Controller
{
    use Monitoring\ControllerTrait;

    public $defaultAction = "batch";

    /** @var array[]|string[]|Monitoring\ControlInterface[] references */
    public $controls = [];

    public function controls(): array
    {
        return $this->controls;
    }

    public function actionExecute(string $id): int
    {
        try {
            $result = $this->executeSingle($id);
        } catch (\OutOfBoundsException $exception) {
            $this->stderr($exception->getMessage());
            return console\ExitCode::DATAERR;
        } catch (base\InvalidConfigException $exception) {
            return $this->handleInvalidConfiguration($exception);
        }
        return $this->handleResult($result);
    }

    public function actionBatch(): int
    {
        try {
            $result = $this->executeBatch();
        } catch (base\InvalidConfigException $exception) {
            return $this->handleInvalidConfiguration($exception);
        }
        return $this->handleResult($result);
    }

    protected function handleResult(Monitoring\Web\View $result): int
    {
        if ($result->getState() === Monitoring\Web\View::STATE_ERROR) {
            $this->stderr('Error', helpers\Console::FG_RED);
            $this->stderr("\t{$result->getTotal()}", helpers\Console::FG_GREY);
            $this->stderr(PHP_EOL . $result->getException() . PHP_EOL);
            return console\ExitCode::PROTOCOL;
        }
        $this->stdout("OK", helpers\Console::FG_GREEN);
        $this->stdout("\t{$result->getTotal()}", helpers\Console::FG_GREY);
        if (!is_null($result->getDetails())) {
            $this->stdout(PHP_EOL . json_encode($result->getDetails(), JSON_PRETTY_PRINT) . PHP_EOL);
        }
        return console\ExitCode::OK;
    }

    protected function handleInvalidConfiguration(base\InvalidConfigException $exception): int
    {
        \Yii::error($exception, static::class);
        $this->stderr($exception);
        return console\ExitCode::CONFIG;
    }
}
