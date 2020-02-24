<?php


namespace Shallowman\Laralog\Formatter;

use Carbon\Carbon;
use Monolog\Formatter\LogstashFormatter as MonologLogstashFormatter;

class LogstashFormatter extends MonologLogstashFormatter
{
    public function __construct(
        string $applicationName,
        string $systemName = null,
        string $extraPrefix = null,
        string $contextPrefix = 'ctxt_',
        int $version = MonologLogstashFormatter::V1
    ) {

        if (null === $applicationName || '' === $applicationName) {
            $applicationName = ($app = config('app.name')) === '' ? 'Laravel' : $app;
        }

        parent::__construct($applicationName, $systemName, $extraPrefix, $contextPrefix, $version);
    }

    public function format(array $record)
    {
        return $this->toJson(array_merge($record, $this->getSpecifiedLogRecord())).PHP_EOL;
    }

    public function getStart()
    {
        $requestTime = request()->server('REQUEST_TIME_FLOAT');

        if (defined('LARAVEL_START')) {
            return Carbon::createFromTimestampMs(bcmul(LARAVEL_START * 1000, 0));
        }

        if ($requestTime === null || $requestTime === '') {
            return now();
        }
        return Carbon::createFromTimestampMs(bcmul($requestTime * 1000, 0));
    }

    public function getEnd()
    {
        return now()->format('Y-m-d H:i:s.u');
    }

    public function getSpecifiedLogRecord(): array
    {
        return [
            'uri'         => '',
            'method'      => '',
            'ip'          => '',
            'platform'    => '',
            'version'     => '',
            'os'          => '',
            'tag'         => '',
            'channel'     => 'frame',
            'start'       => $this->getStart()->format('Y-m-d H:i:s.u'),
            'end'         => $this->getEnd(),
            'parameters'  => '',
            'performance' => round(microtime(true) - $this->getStart()->micro, 3),
            'response'    => '',
        ];
    }

}