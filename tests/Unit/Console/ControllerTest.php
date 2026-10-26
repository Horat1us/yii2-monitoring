<?php

declare(strict_types=1);

namespace Horat1us\Yii\Monitoring\Tests\Unit\Console;

use Horat1us\Yii\Monitoring;
use PHPUnit\Framework\TestCase;
use yii\base;
use yii\console;
use yii\console\ExitCode;
use yii\web\Response;

class ControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        \Yii::$app = null;
        parent::tearDown();
    }

    public function testOptions(): void
    {
        $controller = $this->createController([]);

        $this->assertContains('format', $controller->options('batch'));
        $this->assertEquals('format', $controller->optionAliases()['f']);
    }

    public function testBeforeAction(): void
    {
        $this->mockApplication();
        $controller = $this->createController([], Response::FORMAT_JSON);

        $this->assertTrue($controller->beforeAction(new base\InlineAction('batch', $controller, 'actionBatch')));
        $this->assertEquals('', $controller->errorOutput);
    }

    public function testInvalidFormat(): void
    {
        $this->mockApplication();
        $controller = $this->createController([], 'xml');

        try {
            $controller->beforeAction(new base\InlineAction('batch', $controller, 'actionBatch'));
            $this->fail('Exit exception was not thrown');
        } catch (base\ExitException $exception) {
            $this->assertEquals(ExitCode::CONFIG, $exception->statusCode);
        }
        $this->assertEquals(
            'Invalid output format option value. Available values: raw, json' . PHP_EOL,
            $controller->errorOutput
        );
    }

    public function testExecuteRaw(): void
    {
        $controller = $this->createController([
            'cache' => $this->createControl(['type' => 'cache']),
        ]);

        $this->assertEquals(ExitCode::OK, $controller->actionExecute('cache'));
        $this->assertStringStartsWith("OK\t", $controller->output);
        $this->assertStringEndsWith(
            PHP_EOL . json_encode(['type' => 'cache'], JSON_PRETTY_PRINT) . PHP_EOL,
            $controller->output
        );
        $this->assertEquals('', $controller->errorOutput);
    }

    public function testExecuteRawWithoutDetails(): void
    {
        $controller = $this->createController([
            'db' => $this->createControl(null),
        ]);

        $this->assertEquals(ExitCode::OK, $controller->actionExecute('db'));
        $this->assertMatchesRegularExpression('/^OK\t\S+$/', $controller->output);
        $this->assertEquals('', $controller->errorOutput);
    }

    public function testExecuteJson(): void
    {
        $controller = $this->createController([
            'cache' => $this->createControl(['type' => 'cache']),
        ], Response::FORMAT_JSON);

        $this->assertEquals(ExitCode::OK, $controller->actionExecute('cache'));
        $this->assertStringEndsWith(PHP_EOL, $controller->output);
        $output = json_decode($controller->output, true);
        $this->assertEquals(Monitoring\Web\View::STATE_OK, $output['state']);
        $this->assertEquals(['type' => 'cache'], $output['details']);
        $this->assertEquals('', $controller->errorOutput);
    }

    public function testFailedExecuteRaw(): void
    {
        $controller = $this->createController([
            'cache' => $this->createFailingControl(),
        ]);

        $this->assertEquals(ExitCode::PROTOCOL, $controller->actionExecute('cache'));
        $this->assertEquals('', $controller->output);
        $this->assertStringStartsWith("Error\t", $controller->errorOutput);
        $this->assertStringContainsString('Cache is unavailable', $controller->errorOutput);
    }

    public function testFailedExecuteJson(): void
    {
        $controller = $this->createController([
            'cache' => $this->createFailingControl(),
        ], Response::FORMAT_JSON);

        $this->assertEquals(ExitCode::PROTOCOL, $controller->actionExecute('cache'));
        $output = json_decode($controller->output, true);
        $this->assertEquals(Monitoring\Web\View::STATE_ERROR, $output['state']);
        $this->assertEquals(
            [
                'type' => Monitoring\Exception::class,
                'code' => 101,
                'message' => 'Cache is unavailable',
                'details' => ['type' => 'cache'],
            ],
            $output['error']
        );
        $this->assertEquals('', $controller->errorOutput);
    }

    public function testExecuteMissingControl(): void
    {
        $controller = $this->createController([]);

        $this->assertEquals(ExitCode::DATAERR, $controller->actionExecute('cache'));
        $this->assertEquals('', $controller->output);
        $this->assertEquals('Control cache not found.', $controller->errorOutput);
    }

    public function testExecuteInvalidControlConfiguration(): void
    {
        $controller = $this->createController([
            'queue' => 'invalidControl',
        ]);

        $this->assertEquals(ExitCode::CONFIG, $controller->actionExecute('queue'));
        $this->assertEquals('', $controller->output);
        $this->assertStringContainsString('invalidControl', $controller->errorOutput);
    }

    public function testBatchRaw(): void
    {
        $controller = $this->createController([
            'db' => $this->createControl(null),
            'cache' => $this->createControl(['type' => 'cache']),
        ]);

        $this->assertEquals(ExitCode::OK, $controller->actionBatch());
        $this->assertStringStartsWith("OK\t", $controller->output);
        $this->assertEquals('', $controller->errorOutput);
    }

    public function testFailedBatchJson(): void
    {
        $controller = $this->createController([
            'db' => $this->createControl(null),
            'cache' => $this->createFailingControl(),
        ], Response::FORMAT_JSON);

        $this->assertEquals(ExitCode::PROTOCOL, $controller->actionBatch());
        $output = json_decode($controller->output, true);
        $this->assertEquals(Monitoring\Web\View::STATE_ERROR, $output['state']);
        $this->assertEquals('One or more control failed.', $output['error']['message']);
        $this->assertEquals(Monitoring\Web\View::STATE_OK, $output['error']['details']['db']['state']);
        $this->assertEquals(Monitoring\Web\View::STATE_ERROR, $output['error']['details']['cache']['state']);
        $this->assertEquals('', $controller->errorOutput);
    }

    public function testBatchInvalidControlConfiguration(): void
    {
        $controller = $this->createController([
            'queue' => 'invalidControl',
        ], Response::FORMAT_JSON);

        $this->assertEquals(ExitCode::CONFIG, $controller->actionBatch());
        $this->assertEquals('', $controller->output);
        $this->assertStringContainsString('invalidControl', $controller->errorOutput);
    }

    /**
     * console\Controller::beforeAction() configures the application error handler.
     */
    protected function mockApplication(): void
    {
        new console\Application([
            'id' => 'yii2-monitoring-test',
            'basePath' => dirname(__DIR__, 3),
        ]);
    }

    /**
     * Controller that collects stdout and stderr output instead of writing to the streams.
     */
    protected function createController(
        array $controls,
        string $format = Response::FORMAT_RAW
    ): Monitoring\Console\Controller {
        $module = $this->createStub(base\Module::class);
        $config = [
            'request' => $this->createStub(console\Request::class),
            'response' => $this->createStub(console\Response::class),
        ];
        $controller = new class ('health-check', $module, $config) extends Monitoring\Console\Controller {
            public string $output = '';
            public string $errorOutput = '';

            public function stdout($string)
            {
                $this->output .= $string;
                return strlen($string);
            }

            public function stderr($string)
            {
                $this->errorOutput .= $string;
                return strlen((string)$string);
            }
        };
        $controller->controls = $controls;
        $controller->format = $format;

        return $controller;
    }

    protected function createControl(?array $details): Monitoring\ControlInterface
    {
        $control = $this->createStub(Monitoring\ControlInterface::class);
        $control->method('execute')->willReturn($details);

        return $control;
    }

    protected function createFailingControl(): Monitoring\ControlInterface
    {
        $control = $this->createStub(Monitoring\ControlInterface::class);
        $control->method('execute')->willThrowException(
            new Monitoring\Exception('Cache is unavailable', 101, ['type' => 'cache'])
        );

        return $control;
    }
}
