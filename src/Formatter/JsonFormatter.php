<?php

declare(strict_types=1);

namespace Shallowman\Laralog\Formatter;

use Carbon\Carbon;
use Exception;
use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;
use Shallowman\Laralog\Traits\ClipLogTrait;

class JsonFormatter extends MonologJsonFormatter
{
    use ClipLogTrait;
    public const POSITIVE_INFINITY = 'POSITIVE_INFINITY';
    // default app name if not set
    public const DEFAULT_APP_NAME = 'laravel';
    // default log level if not set
    public const DEFAULT_LOG_LEVEL = 'info';
    // default app environment if not set
    public const DEFAULT_APP_ENV = 'local';
    // default log channel if not set
    public const DEFAULT_LOG_CHANNEL = 'default';
    // default host name if not detect
    public const UNKNOWN_HOST = 'unknown_host';

    /**
     * override parent func.
     */
    public function format(array $record): string
    {
        $normalizedRecord = $this->normalizeRecord($record);
        $normalized = $this->tailor($normalizedRecord, $record['context'] ?? []);

        return $this->toJson($normalized).PHP_EOL;
    }

    /**
     * tailor record array.
     */
    public function tailor(array $normalizedRecord, array $context): array
    {
        $keys = array_keys($normalizedRecord);

        return array_merge($normalizedRecord, array_filter(
            $context,
            static function ($v, $k) use ($keys) {
                return in_array($k, $keys, true) && is_string($v);
            },
            ARRAY_FILTER_USE_BOTH
        )
        );
    }

    /**
     * normalize record data using standard definition.
     *
     * @return void
     */
    public function normalizeRecord(array $record): array
    {
        return [
            '@timestamp' => $this->getCurrentESTimestamp(),
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
            'start' => Carbon::createFromTimestampMs(self::getStartMicroTimestamp() * 1000)->format('Y-m-d H:i:s.u'),
            'end' => now()->format('Y-m-d H:i:s.u'),
            'parameters' => '',
            'performance' => round(microtime(true) - self::getStartMicroTimestamp(), 6),
            'response' => '',
            'extra' => self::clipLog($this->normalizeExtra($record['context'] ?? [])),
            'msg' => self::clipLog(!is_string($record['message']) ? serialize($record['message']) : $record['message']),
            'headers' => '',
            'hostname' => gethostname() ?: self::UNKNOWN_HOST,
            'tag' => static::label(),
        ];
    }

    public static function getStartMicroTimestamp(): float
    {
        if (defined('LARAVEL_START')) {
            return LARAVEL_START;
        }

        if (!function_exists('request')) {
            return microtime(true);
        }

        $timestamp = request()->server('REQUEST_TIME_FLOAT');
        if (is_float($timestamp) || (is_string($timestamp) && '' !== $timestamp)) {
            return (float) $timestamp;
        }

        return microtime(true);
    }

    public static function getCurrentESTimestamp(): string
    {
        return now()->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z');
    }

    /**
     * capture and render exception traces.
     */
    public function normalizeExtra(array $context): string
    {
        if (isset($context['exception']) && ($context['exception'] instanceof Exception)) {
            $context['stacktrace'] = $context['exception']->getTraceAsString();
            $context['error_msg'] = $context['exception']->getMessage();
            unset($context['exception']);
        }

        return $this->toJson($context ?? [], true);
    }
}
