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

    /**
     * Rewrite monolog json formatter
     *
     * @param array $record
     *
     * @return string
     */
    public function format(array $record): string
    {
        $context = $record['context'] ?? [];
        $record = $this->customize($record);
        $formatContext = $this->filterDuplicateKeys($context, array_keys($record));
        return $this->toJson(array_merge($record, $formatContext)) . PHP_EOL;
    }

    public function filterDuplicateKeys(array $context, array $keys): array
    {
        return array_filter($context, static function ($key) use ($keys) {
            return in_array($key, $keys, true);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Customize log record content
     *
     * @param array $record
     *
     * @return array
     */
    public function customize(array $record): array
    {
        return [
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
}
