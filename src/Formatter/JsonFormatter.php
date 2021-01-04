<?php

namespace Shallowman\Laralog\Formatter;

use Carbon\Carbon;
use Exception;
use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;

class JsonFormatter extends MonologJsonFormatter
{
    public const DEFAULT_APP_NAME = 'LARAVEL';

    public const DEFAULT_LOG_LEVEL = 'INFO';

    public const DEFAULT_APP_ENV = 'PRODUCTION';

    public const DEFAULT_LOG_CHANNEL = 'DEFAULT';

    public const UNKNOWN_HOST = 'UNKNOWN HOST';

    private $record;

    private $context;

    /**
     * Rewrite monolog json formatter
     *
     * @param array $record
     *
     * @return string
     */
    public function format(array $record): string
    {
        $this->setContext($record['context'] ?? []);
        $this->setRecord($record);
        $this->mergeRecord();
        return $this->toJson($this->record) . PHP_EOL;
    }

    /**
     * Merge context to record array
     */
    protected function mergeRecord(): void
    {
        $keys = array_keys($this->record);
        $this->record = array_merge($this->record, array_filter($this->context, static function ($v, $k) use ($keys) {
            return \in_array($k, $keys, true) && \is_string($v);
        }, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Customize log record content
     *
     * @param array $record
     * @return void
     */
    public function setRecord(array $record): void
    {
        $this->record = [
            '@timestamp' => $this->getFriendlyElasticSearchTimestamp(),
            'app' => config('app.name') ?? self::DEFAULT_APP_NAME,
            'env' => config('app.env') ?? self::DEFAULT_APP_ENV,
            'level' => $record['level_name'] ?? self::DEFAULT_LOG_LEVEL,
            'logChannel' => $record['channel'] ?? self::DEFAULT_LOG_CHANNEL,
            'channel' => 'frame',
            'uri' => '',
            'method' => '',
            'ip' => '',
            'platform' => '',
            'version' => '',
            'os' => '',
            'tag' => '',
            'start' => Carbon::createFromTimestampMs($this->getStartMicroTimestamp() * 1000)->format('Y-m-d H:i:s.u'),
            'end' => now()->format('Y-m-d H:i:s.u'),
            'parameters' => '',
            'performance' => round(microtime(true) - $this->getStartMicroTimestamp(), 6),
            'response' => '',
            'extra' => $this->handleExtra($record['context'] ?? []),
            'msg' => $record['message'],
            'headers' => '',
            'hostname' => gethostname() ?: self::UNKNOWN_HOST,
        ];
    }

    private function getStartMicroTimestamp(): float
    {
        if (defined('LARAVEL_START')) {
            return LARAVEL_START;
        }

        if ($timestamp = request()->server('REQUEST_TIME_FLOAT')) {
            return $timestamp;
        }

        return microtime(true);
    }

    public function getFriendlyElasticSearchTimestamp(): string
    {
        return now()->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z');
    }

    /**
     * Add exception message and trace info to log record when meet exception
     *
     * @param array $context
     *
     * @return string
     */
    public function handleExtra(array $context): string
    {
        if (isset($context['exception']) && ($context['exception'] instanceof Exception)) {
            $context['stacktrace'] = $context['exception']->getTrace();
            $context['error_msg'] = $context['exception']->getMessage();
            unset($context['exception']);
        }

        return $this->toJson($context ?? [], true);
    }

    /**
     * @param array $context
     */
    private function setContext(array $context): void
    {
        $this->context = $context;
    }
}
