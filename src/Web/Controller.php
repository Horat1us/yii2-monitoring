<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Web;

use Horat1us\Yii\Monitoring;
use yii\base;
use yii\web;

/**
 * Class Controller
 * @package Horat1us\Yii\Monitoring\Web
 */
class Controller extends web\Controller
{
    use Monitoring\ControllerTrait;

    public const CODE_INVALID_CONFIGURATION = -1;
    public const CODE_NOT_FOUND = -2;

    /** @var string */
    public $defaultAction = 'control';

    /** @var array[]|string[]|Monitoring\ControlInterface[] references */
    public $controls = [];

    public function controls(): array
    {
        return $this->controls;
    }

    /**
     * @param string $id control ID
     * @return array
     * @throws web\HttpException
     */
    public function actionControl(string $id): array
    {
        try {
            return $this->executeSingle($id)->jsonSerialize();
        } catch (\OutOfBoundsException $exception) {
            throw new web\NotFoundHttpException(
                $exception->getMessage(),
                static::CODE_NOT_FOUND,
                $exception
            );
        } catch (base\InvalidConfigException $e) {
            throw new web\HttpException(
                503,
                "Control {$id} has invalid configuration",
                static::CODE_INVALID_CONFIGURATION,
                $e
            );
        }
    }

    /**
     * @return array
     * @throws web\HttpException
     */
    public function actionFull(): array
    {
        try {
            return $this->executeBatch()->jsonSerialize();
        } catch(base\InvalidConfigException $exception) {
            throw new web\HttpException(
                503,
                "Invalid controls configuration.",
                static::CODE_INVALID_CONFIGURATION,
                $exception
            );
        }
    }
}
