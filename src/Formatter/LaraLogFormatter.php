<?php


namespace Shallowman\Laralog\Formatter;


class LaraLogFormatter
{
    /**
     * @param \Monolog\Logger $logger
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LogstashFormatter(config('app.name')));
        }
    }
}