<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Control;

use yii\di;
use yii\base;
use Horat1us\Yii\Monitoring;

/**
 * Class Dependency
 * @package Horat1us\Yii\Monitoring\Control
 */
class Dependency extends Monitoring\Control
{
    /** @var array[]|string[]|Dependency\ItemInterface[] */
    public $dependencies = [];

    /** @var array|string|Monitoring\ControlInterface */
    public $control;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->dependencies = array_map(function ($reference): Dependency\ItemInterface {
            /** @var Dependency\ItemInterface $item */
            $item = di\Instance::ensure($reference, Dependency\ItemInterface::class);
            return $item;
        }, $this->dependencies);
        $this->control = di\Instance::ensure($this->control, Monitoring\ControlInterface::class);
    }

    public function execute(): ?array
    {
        foreach ($this->dependencies as $dependency) {
            if (!$dependency->match()) {
                $details = [
                    'executed' => false,
                    'dependency' => get_class($dependency),
                ];
                if ($dependency instanceof \JsonSerializable) {
                    $details += $dependency->jsonSerialize();
                }
                return $this->details() + $details;
            }
        }

        return $this->control->execute();
    }

    protected function details(): array
    {
        return [
            'type' => get_class($this->control),
        ];
    }
}
