<?php

namespace Shallowman\Laralog\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Shallowman\Laralog\LaraLogger;
use Symfony\Component\HttpFoundation\Response;

class CaptureRequestLifecycle
{
    protected $env;

    protected $app;

    protected $channel;

    protected $level;

    protected $uri;

    protected $logChannel;

    protected $method;

    protected $ip;

    protected $version;

    protected $platform;

    protected $os;

    protected $tag;

    protected $start;

    protected $end;

    protected $performance;

    protected $msg;

    protected $response;

    protected $parameters;

    protected $log;

    protected $headers;

    protected $hostname;

    public $extra;

    private $timestamp;

    public function __construct(LaraLogger $log)
    {
        $this->log = $log;
    }

    /**
     * Middleware to hand http request from this to next
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    /**
     * @param Request $request
     * @param Response $response
     * Capture http lifecycle context and write to log with json format
     */
    public function terminate($request, $response): void
    {
        $this->setRequestLifecycleVariables($request, $response);
        $this->log->info('', $this->captureLifecycle());
    }

    private function getStartMicroTimestamp(Request $request)
    {
        if (defined('LARAVEL_START')) {
            return LARAVEL_START;
        }

        if ($timestamp = $request->server('REQUEST_TIME_FLOAT')) {
            return $timestamp;
        }

        return microtime(true);
    }

    private function setTimestamp(string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function setParameters(string $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function setExtra(string $extra = ''): void
    {
        $this->extra = $extra;
    }

    public function setPlatform(string $platform = ''): void
    {
        $this->platform = $platform;
    }

    public function setVersion(string $version = ''): void
    {
        $this->version = $version;
    }

    public function setOs(string $os = ''): void
    {
        $this->os = $os;
    }

    public function setTag(string $tag = ''): void
    {
        $this->tag = $tag;
    }

    protected function setAppName(string $app): void
    {
        $this->app = $app;
    }

    protected function setEnv(string $env): void
    {
        $this->env = $env;
    }

    protected function setChannel(string $channel = 'app'): void
    {
        $this->channel = $channel;
    }

    protected function setLevel(string $level = 'INFO'): void
    {
        $this->level = $level;
    }

    protected function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    protected function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    protected function setMethod(string $method): void
    {
        $this->method = $method;
    }

    protected function setStart(string $start): void
    {
        $this->start = $start;
    }

    protected function setEnd(string $end): void
    {
        $this->end = $end;
    }

    protected function setResponse(string $response): void
    {
        $this->response = $response;
    }

    protected function setLogChannel(string $logChannel = 'middleware'): void
    {
        $this->logChannel = $logChannel;
    }

    protected function setPerformance(float $performance): void
    {
        $this->performance = $performance;
    }

    public function setMessage(string $message = ''): void
    {
        $this->msg = $message;
    }

    protected function setHeaders(string $headers): void
    {
        $this->headers = $headers;
    }

    protected function setHostname(string $hostname): void
    {
        $this->hostname = $hostname;
    }

    public function setRequestLifecycleVariables(Request $request, Response $response): void
    {
        $uri = $request->getUri();
        $parameters = collect($request->except(config('laralog.except')))->toJson();
        if (self::isExceptUri($uri)) {
            $parameters = '';
        }
        $this->setAppName(config('app.name') ?? 'Laravel');
        $this->setChannel();
        $this->setEnv(config('app.env') ?? 'Unknown');
        $this->setLogChannel();
        $this->setLevel();
        $this->setOs();
        $this->setPlatform();
        $this->setTag();
        $this->setUri($uri);
        $this->setMethod($request->getMethod());
        $this->setIp(implode(',', $request->getClientIps()));
        $this->setVersion();
        $this->setParameters($parameters);
        $this->setStart(
            Carbon::createFromTimestampMs($this->getStartMicroTimestamp($request) * 1000)->format('Y-m-d H:i:s.u')
        );
        $this->setEnd(now()->format('Y-m-d H:i.s.u'));
        $this->setPerformance(round(microtime(true) - $this->getStartMicroTimestamp($request), 6));
        $this->setResponse($response->getContent());
        $this->setMessage();
        $this->setTimestamp(now()->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z'));
        $this->setExtra();
        $this->setHeaders(collect($request->headers->all())->toJson());
        $this->setHostname(gethostname() ?: 'Unknown Hostname');
    }

    /**
     * Capture request lifecycle content
     *
     * @return array
     */
    public function captureLifecycle(): array
    {
        return [
            '@timestamp' => $this->timestamp,
            'app' => $this->app,
            'env' => $this->env,
            'channel' => $this->channel,
            'logChannel' => $this->logChannel,
            'uri' => $this->uri,
            'method' => $this->method,
            'ip' => $this->ip,
            'platform' => $this->platform,
            'version' => $this->version,
            'os' => $this->os,
            'level' => $this->level,
            'tag' => $this->tag,
            'start' => $this->start,
            'end' => $this->end,
            'parameters' => $this->parameters,
            'performance' => $this->performance,
            'msg' => $this->msg,
            'response' => $this->response,
            'extra' => $this->extra,
            'headers' => $this->headers,
            'hostname' => $this->hostname,
        ];
    }

    public static function isExceptUri(string $uri): bool
    {
        return Str::contains($uri, config('laralog.except.uri'));
    }
}
