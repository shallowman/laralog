<?php

declare(strict_types=1);

namespace Shallowman\Laralog\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function mb_substr;
use Psr\Log\LoggerInterface;
use Shallowman\Laralog\LaraLogger;
use Symfony\Component\HttpFoundation\Response;

class CaptureRequestLifecycle
{
    public const POSITIVE_INFINITY = 'POSITIVE_INFINITY';

    public const DEFAULT_CLIPPED_LENGTH = 1000;

    public const SHOULD_TAGS = [
        'exceptedUri' => 'shouldCapture',
        'clipped' => 'shouldClipped',
    ];

    protected LoggerInterface $log;

    public function __construct(LaraLogger $log)
    {
        $this->log = $log;
    }

    /**
     * terminate middleware to capture and compose http lifecycle info.
     *
     * @param $request
     * @param $response
     */
    public function terminate($request, $response): void
    {
        $context = $this->captureAndComposeRequiredVariables($request, $response);
        $this->log->info('', $context);
    }

    /**
     * retrieve the start time of laravel framework or http-request income.
     */
    public static function getStartMicroTimestamp(Request $request): float
    {
        if (defined('LARAVEL_START')) {
            return LARAVEL_START;
        }
        $timestamp = $request->server('REQUEST_TIME_FLOAT');
        if (is_float($timestamp) || (is_string($timestamp) && '' !== $timestamp)) {
            return (float) $timestamp;
        }

        return microtime(true);
    }

    public function captureAndComposeRequiredVariables(Request $request, Response $response): array
    {
        $uri = $request->getUri();

        return [
            '@timestamp' => now()->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z'),
            'app' => config('app.name') ?? $request->getHttpHost(),
            'env' => config('app.env') ?? 'test',
            'level' => 'info',
            'logChannel' => 'middleware',
            'channel' => 'frame',
            'uri' => $uri,
            'method' => $request->getMethod(),
            'ip' => implode(',', $request->getClientIps()),
            'platform' => '',
            'version' => '',
            'os' => '',
            'tag' => '',
            'start' => Carbon::createFromTimestampMs(static::getStartMicroTimestamp($request) * 1000)->format('Y-m-d H:i:s.u'),
            'end' => now()->format('Y-m-d H:i:s.u'),
            'parameters' => self::clipLog(collect($request->except(config('laralog.capture.except.http_req_fields')))->toJson()),
            'performance' => round(microtime(true) - static::getStartMicroTimestamp($request), 6),
            'response' => self::shouldCapture() ? '' : self::clipLog($response->getContent()),
            'extra' => '',
            'msg' => '',
            'headers' => '',
            'hostname' => gethostname() ?: 'unknown_host',
        ];
    }

    /**
     * determine if the uri should capture the response body info or not.
     */
    public static function shouldCapture(): bool
    {
        $exceptUris = config('laralog.capture.except.uri');

        if (null === $exceptUris || (is_array($exceptUris) && empty($exceptUris))) {
            return false;
        }

        return Str::contains(request()->getUri(), $exceptUris);
    }

    public static function shouldClipped(): bool
    {
        return self::POSITIVE_INFINITY !== config('laralog.log_clipped_length');
    }

    public static function clipLog(string $log): string
    {
        if (!self::shouldClipped()) {
            return $log;
        }
        $length = config('laralog.log_clipped_length');
        $length = is_numeric($length) ? (int) $length : self::DEFAULT_CLIPPED_LENGTH;

        return mb_substr($log, 0, $length).'...clipped';
    }

    public static function label(): string
    {
        return implode(',', array_keys(array_filter(self::SHOULD_TAGS, function ($v) {
            return call_user_func([self::class, $v]);
        })));
    }
}
