<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Web;

use yii\di;
use yii\web;
use yii\base;
use Horat1us\Yii\Monitoring;

/**
 * Class Controller
 * @package Horat1us\Yii\Monitoring\Web
 */
class Controller extends web\Controller
{
    public const CODE_NOT_FOUND = -1;
    public const CODE_INVALID_CONFIGURATION = -2;

    public $defaultAction = 'control';

    /** @var array[]|string[]|Monitoring\ControlInterface[] references */
    public $controls = [];

    public function controls(): array
    {
        return $this->controls;
    }

    /**
     * @param string $id
     * @return array
     * @throws web\HttpException
     */
    public function actionControl(string $id): array
    {
        $response = new View;
        $control = $this->instantiateControl($id);

        try {
            return $response->success(
                $control->execute()
            )->jsonSerialize();
        } catch (\Throwable $e) {
            \Yii::error($e, static::class);
            return $response->fail($e)->jsonSerialize();
        }
    }

    /**
     * @return array
     * @throws web\HttpException
     */
    public function actionFull(): array
    {
        $response = new View;
        $isFail = false;
        $details = [];

        foreach ($this->controls() as $id => $reference) {
            $control = $this->instantiateControl($id);
            $childResponse = new View;

            try {
                $childResponse->success($control->execute());
            } catch (\Throwable $e) {
                \Yii::error($e, static::class);
                $isFail = true;
                $childResponse->fail($e);
            }

            $details[$id] = $childResponse->jsonSerialize();
        }

        if ($isFail) {
            $response->fail(new Monitoring\Exception(
                "One or more control failed.",
                0,
                $details
            ));
        } else {
            $response->success($details);
        }

        return $response->jsonSerialize();
    }

    /**
     * @param string $id
     * @return Monitoring\ControlInterface
     * @throws web\HttpException
     */
    protected function instantiateControl(string $id, array $reference = null): Monitoring\ControlInterface
    {
        if (is_null($reference)) {
            $reference = $this->controls()[$id] ?? null;
            if (is_null($reference)) {
                throw new web\NotFoundHttpException(
                    "Control {$id} not found.",
                    static::CODE_INVALID_CONFIGURATION
                );
            }
        }

        try {
            /** @var Monitoring\ControlInterface $control */
            $control = di\Instance::ensure($reference, Monitoring\ControlInterface::class);
        } catch (base\InvalidConfigException $e) {
            throw new web\HttpException(
                503,
                "Control {$id} has invalid configuration",
                static::CODE_NOT_FOUND,
                $e
            );
        }

        return $control;
    }
}
