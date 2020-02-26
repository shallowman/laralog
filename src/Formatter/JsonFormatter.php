<?php

namespace Shallowman\Laralog\Formatter;

use Carbon\Carbon;
use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;

class JsonFormatter extends MonologJsonFormatter
{
    public function format(array $record)
    {
        return $this->toJson(array_merge($this->pruneLogRecord($record), $record['context'])).PHP_EOL;
    }

    public function pruneLogRecord(array $record): array
    {
        return [
            '@timestamp'  => $this->getFriendlyElasticSearchTimestamp(),
            'app'         => config('app.name') ?? 'Laravel',
            'env'         => config('app.env') ?? 'Production',
            'level'       => $record['level_name'],
            'logChannel'  => $record['channel'],
            'channel'     => 'frame',
            'uri'         => '',
            'method'      => '',
            'ip'          => '',
            'platform'    => '',
            'version'     => '',
            'os'          => '',
            'tag'         => '',
            'start'       => Carbon::createFromTimestampMs($this->getStartMicroTimestamp() * 1000)->format('Y-m-d H:i:s.u'),
            'end'         => now()->format('Y-m-d H:i:s.u'),
            'parameters'  => '',
            'performance' => round(microtime(true) - $this->getStartMicroTimestamp(), 6),
            'response'    => '',
            'extra'       => $record['exception'] ?? '',
        ];
    }

    private function getStartMicroTimestamp()
    {
        if (defined('LARAVEL_START')) {
            return LARAVEL_START;
        }

        if ($timestamp = request()->server('REQUEST_TIME_FLOAT')) {
            return $timestamp;
        }

        return microtime(true);
    }

    public function getFriendlyElasticSearchTimestamp()
    {
        return now()->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z');
    }
}