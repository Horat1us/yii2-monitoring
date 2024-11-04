<?php

namespace Horat1us\Yii\Monitoring;

use yii\base;
use yii\di;

/**
 * Trait ControllerTrait
 * @package Horat1us\Yii\Monitoring
 * #todo: Should be implemented as service class instead of controller trait?
 */
trait ControllerTrait
{
    /**
     * Returns references array with key as control ID
     * @return array[]|string[]|ControlInterface[]
     */
    abstract public function controls(): array;

    /**
     * @param string $controlId
     * @return Web\View
     * @throws base\InvalidConfigException
     * @throws \OutOfBoundsException
     */
    protected function executeSingle(string $controlId): Web\View
    {
        $response = new Web\View();
        $control = $this->instantiateControl($controlId);

        try {
            return $response->success(
                $control->execute()
            );
        } catch (\Throwable $e) {
            \Yii::error((string)$e, static::class);
            return $response->fail($e);
        }
    }

    /**
     * @return Web\View
     * @throws base\InvalidConfigException
     */
    protected function executeBatch(): Web\View
    {
        $response = new Web\View();
        $isOk = true;
        $details = [];

        foreach (array_keys($this->controls()) as $id) {
            $details[$id] = $result = $this->executeSingle($id);
            if ($result->getState() === Web\View::STATE_ERROR) {
                $isOk = false;
            }
        }

        if ($isOk) {
            return $response->success($details);
        }
        return $response->fail(new Exception("One or more control failed.", 0, $details));
    }

    /**
     * @param string $id
     * @return ControlInterface
     * @throws base\InvalidConfigException
     * @throws \OutOfBoundsException
     */
    protected function instantiateControl(string $id): ControlInterface
    {
        $reference = $this->controls()[$id] ?? null;
        if (is_null($reference)) {
            throw new \OutOfBoundsException(
                "Control {$id} not found."
            );
        }

        /** @var ControlInterface $control */
        $control = di\Instance::ensure($reference, ControlInterface::class);
        return $control;
    }
}
