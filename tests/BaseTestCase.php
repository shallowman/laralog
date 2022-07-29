<?php

namespace Shallowman\Laralog\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use Monolog\Handler\TestHandler;
use Shallowman\Laralog\LaraLogger;

class BaseTestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Define routes setup.
     *
     * @param Router $router
     *
     * @return void
     */

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', 'Shallowman\Laralog\Tests\Http\Kernel');
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Illuminate\Log\LogServiceProvider::class,
            \Shallowman\Laralog\ServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('logging.default', 'laralog');
        $app['config']->set('logging.channels.laralog', [
            // Monolog 提供的 driver,保留不变
            'driver' => 'monolog',
            // channel 名称，要与数组键名保持一致
            'name' => 'daily',
            // 日志存储路径，及日志文件命名
            'path' => storage_path('logs/laralog.log'),
            // 指定使用的日志格式化组件类
            'tap' => [\Shallowman\Laralog\Formatter\LaralogFormatter::class],
            'handler' => TestHandler::class,
            'level' => 'info',
            // 日志文件保留天数
            'days' => 7,
            // 在写日志文件前获得锁，避免并发写入造成的争抢，写错行
            'locking' => true,
        ]);
        $app['config']->set('laralog', [
            'except' => [
                // The fields filled in the blow array will be excluded to print in the log which in the http request body carried items
                // perfect match
                'fields' => [
                    'password',
                    'password_information',
                    'password_confirm',
                ],
                // The uris filled in the blow array will be excluded to print http response body
                // Using full fuzzy matching
                'uris' => [
                    '/welcome',
                ],
            ],
            'log_clipped_length' => env('LARALOG_CLIPPED_LENGTH', 500),
        ]);

        $app['config']->set('logging.channels.emergency', [
            'path' => storage_path('logs/laravel.log'),
        ]);
    }

    protected function defineRoutes($router)
    {
        $router->middleware('api')->group(function ($router) {
            $router->get('test/case1', function () {
                return ['case1' => 'case1'];
            });
            $router->get('test/case2', function () {
                return ['case2' => Str::random(1111)];
            });
        });
    }

    protected function getLaraLogger()
    {
        return $this->app->make(LaraLogger::class);
    }
}
