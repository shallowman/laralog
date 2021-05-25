<?php

namespace Shallowman\Laralog;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Log\LogManager as LaravelLogManager;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as Monolog;

class LaraLogger extends LaravelLogManager
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function createDailyDriver(array $config): Monolog
    {
        return new Monolog(
            $this->parseChannel($config),
            [
                $this->prepareHandler(
                    new RotatingFileHandler(
                        $this->generateLogFilenameViaHostname(config('path')),
                        $config['days'] ?? 7,
                        $this->level($config),
                        $config['bubble'] ?? true,
                        $config['permission'] ?? null,
                        $config['locking'] ?? false
                    ),
                    $config
                ),
            ]
        );
    }

    private function generateLogFilenameViaHostname(string $path): string
    {
        if ($host = gethostname()) {
            return $path . '-' . $host;
        }

        return $path;
    }
}
