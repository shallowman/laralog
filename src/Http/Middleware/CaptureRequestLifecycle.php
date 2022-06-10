<?php

declare(strict_types=1);

namespace Shallowman\Laralog\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Shallowman\Laralog\LaraLogger;

class CaptureRequestLifecycle
{
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
            return (float)$timestamp;
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
            'parameters' => collect($request->except(config('laralog.capture.except.http_req_fields')))->toJson(),
            'performance' => round(microtime(true) - static::getStartMicroTimestamp($request), 6),
            'response' => self::shouldCapture($uri) ? '' : $response->getContent(),
            'extra' => '',
            'msg' => '',
            'headers' => '',
            'hostname' => gethostname() ?: 'unknown_host',
        ];
    }

    /**
     * determine if the uri should capture the response body info or not.
     */
    public static function shouldCapture(string $uri): bool
    {
        $exceptUris = config('laralog.capture.except.uri');

        if (null === $exceptUris || (is_array($exceptUris) && empty($exceptUris))) {
            return false;
        }

        return Str::contains($uri, $exceptUris);
    }
}
