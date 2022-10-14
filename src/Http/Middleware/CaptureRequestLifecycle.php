<?php

declare(strict_types=1);

namespace Shallowman\Laralog\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Shallowman\Laralog\LaraLogger;
use Shallowman\Laralog\Traits\ClipLogTrait;
use Symfony\Component\HttpFoundation\Response;

class CaptureRequestLifecycle
{
    use ClipLogTrait;

    public const POSITIVE_INFINITY = 'POSITIVE_INFINITY';

    public const DEFAULT_CLIPPED_LENGTH = 1000;

    protected $log;

    public function __construct(LaraLogger $log)
    {
        $this->log = $log;
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
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
        return [
            '@timestamp' => now()->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z'),
            'app' => config('app.name') ?? $request->getHttpHost(),
            'env' => config('app.env') ?? 'test',
            'level' => 'info',
            'logChannel' => 'middleware',
            'channel' => 'frame',
            'uri' => $request->getUri(),
            'method' => $request->getMethod(),
            'ip' => implode(',', $request->getClientIps()),
            'platform' => '',
            'version' => '',
            'os' => '',
            'start' => Carbon::createFromTimestampMs(static::getStartMicroTimestamp($request) * 1000)->format('Y-m-d H:i:s.u'),
            'end' => now()->format('Y-m-d H:i:s.u'),
            'parameters' => self::clipLog(self::requestToString($request)),
            'performance' => round(microtime(true) - static::getStartMicroTimestamp($request), 6),
            'response' => self::shouldCapture($request) ? '' : self::clipLog(self::responseToString($response->getContent())),
            'extra' => '',
            'msg' => '',
            'headers' => self::headers($request),
            'hostname' => gethostname() ?: 'unknown_host',
            'tag' => static::label(),
        ];
    }

    /**
     * determine if the uri should capture the response body info or not.
     */
    public static function shouldCapture(Request $request): bool
    {
        $exceptUris = config('laralog.except.uris');

        if (null === $exceptUris || (is_array($exceptUris) && empty($exceptUris))) {
            return false;
        }

        return static::$shouldLabelExceptedUriTag = Str::contains($request->getUri(), $exceptUris);
    }

    public static function responseToString($response): string
    {
        if (!is_string($response)) {
            return var_export($response, true) ?? '';
        }

        return $response;
    }

    public static function requestToString(Request $request): string
    {
        $json = collect($request->except(config('laralog.except.fields')))->toJson();
        if (!$json) {
            return serialize($request->except(config('laralog.except.fields')));
        }

        return $json;
    }

    public static function headers(Request $request): string
    {
        $headers = $request->header();

        if (null === $headers) {
            return 'null';
        }

        if (is_string($headers)) {
            return $headers;
        }

        if (is_array($headers)) {
            return collect($headers)->toJson();
        }

        return serialize($headers);
    }
}
