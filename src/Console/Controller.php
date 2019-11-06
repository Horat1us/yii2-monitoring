<?php

namespace Horat1us\Yii\Monitoring\Console;

use Horat1us\Yii\Monitoring;
use yii\helpers;
use yii\console;
use yii\base;
use yii\web;

/**
 * Class Controller
 * @package Horat1us\Yii\Monitoring\Console
 */
class Controller extends console\Controller
{
    use Monitoring\ControllerTrait;

    public $defaultAction = "batch";

    public $format = web\Response::FORMAT_RAW;

    /** @var array[]|string[]|Monitoring\ControlInterface[] references */
    public $controls = [];

    public function controls(): array
    {
        return $this->controls;
    }

    public function options($actionID)
    {
        $options = [
            'format'
        ];
        return array_merge(parent::options($actionID), $options);
    }

    public function optionAliases(): array
    {
        $optionAliases = [
            'f' => 'format',
        ];
        return array_merge(parent::optionAliases(), $optionAliases);
    }

    /**=
     * @throws base\ExitException
     */
    public function beforeAction($action): bool
    {
        $beforeAction = parent::beforeAction($action);
        $this->validateFormat();
        return $beforeAction;
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
        if ($this->format === web\Response::FORMAT_JSON) {
            $this->stdout(json_encode($result) . PHP_EOL);
        }
        if ($result->getState() === Monitoring\Web\View::STATE_ERROR) {
            if ($this->format === web\Response::FORMAT_RAW) {
                $this->stderr('Error', helpers\Console::FG_RED);
                $this->stderr("\t{$result->getTotal()}", helpers\Console::FG_GREY);
                $this->stderr(PHP_EOL . $result->getException() . PHP_EOL);
            }
            return console\ExitCode::PROTOCOL;
        }
        if ($this->format === web\Response::FORMAT_RAW) {
            $this->stdout("OK", helpers\Console::FG_GREEN);
            $this->stdout("\t{$result->getTotal()}", helpers\Console::FG_GREY);
            if (!is_null($result->getDetails())) {
                $this->stdout(PHP_EOL . json_encode($result->getDetails(), JSON_PRETTY_PRINT) . PHP_EOL);
            }
        }
        return console\ExitCode::OK;
    }

    protected function handleInvalidConfiguration(base\InvalidConfigException $exception): int
    {
        \Yii::error($exception, static::class);
        $this->stderr($exception);
        return console\ExitCode::CONFIG;
    }

    /**
     * @throws base\ExitException
     */
    protected function validateFormat(): void
    {
        $availableValues = [
            web\Response::FORMAT_RAW,
            web\Response::FORMAT_JSON
        ];

        if (!in_array($this->format, $availableValues)) {
            $message = "Invalid output format option value. Available values: " . implode(", ", $availableValues);
            $this->stderr($message . PHP_EOL, helpers\Console::FG_RED);

            throw new base\ExitException(console\ExitCode::CONFIG, $message);
        }
    }
}
