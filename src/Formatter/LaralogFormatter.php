<?php


namespace Shallowman\Laralog\Formatter;

class LaralogFormatter
{
    /**
     * @param \Monolog\Logger $logger
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter());
        }
    }
}
