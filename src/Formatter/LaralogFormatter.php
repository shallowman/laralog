<?php

namespace Shallowman\Laralog\Formatter;

use Monolog\Logger;

class LaralogFormatter
{
    /**
     * @param Logger $logger
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter());
        }
    }
}
