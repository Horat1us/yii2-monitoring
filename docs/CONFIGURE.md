# Yii2 Monitoring Configuration

To use monitoring in your web application you need to append controller to your application or module
controller map.

## Example

```php
<?php

// config.php

use Horat1us\Yii\Monitoring;

return [
    'controllerMap' => [
        'monitoring' => [
            'class' =>   Monitoring\Web\Controller::class,
            'controls' => [
                // here paste your controls referense
                'id' => Monitoring\ControlInterface::class,     
            ],
        ],    
    ],
];
```

See [usage documentation](./USAGE.md) for built-in controls details.
