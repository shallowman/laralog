<?php

declare(strict_types=1);

namespace Shallowman\Laralog\Processor;

use Illuminate\Log\Logger;

class LaralogProcessor
{
    public function __invoke(Logger $logger)
    {
        $uid = new UidProcessor(12);
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor($uid);
        }
    }
}
