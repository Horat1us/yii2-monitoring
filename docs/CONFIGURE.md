# Yii2 Monitoring Configuration

To use monitoring in your web or console application you need to append 
controller reference to your controller map.

## Example

### Web Application
```php
<?php

// config.php

use Horat1us\Yii\Monitoring;

return [
    'controllerMap' => [
        'monitoring' => [
            'class' =>   Monitoring\Web\Controller::class,
            'controls' => [
                // here paste your controls references
                'id' => Monitoring\ControlInterface::class,     
            ],
        ],    
    ],
];
```

### Console Application
```php
<?php

// config.php

use Horat1us\Yii\Monitoring;

return [
    'controllerMap' => [
        'health-check' => [
            'class' =>   Monitoring\Console\Controller::class,
            'controls' => [
                // here paste your controls references
                'id' => Monitoring\ControlInterface::class,     
            ],
        ],    
    ],
];
```
Then use:
```bash
php yii health-check # Execute all controls
php yii health-check/execute id # Execute single control
```
Not zero return code will be returned in case some control failed.  
*Tip: you may use to monitor Docker container*  

See [usage documentation](./USAGE.md) for built-in controls details.
