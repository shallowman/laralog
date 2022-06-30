<?php

declare(strict_types=1);

namespace Shallowman\Laralog\Formatter;

use Illuminate\Log\Logger;

class LaralogFormatter
{
    public function __invoke(Logger $logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter());
        }
    }
}
