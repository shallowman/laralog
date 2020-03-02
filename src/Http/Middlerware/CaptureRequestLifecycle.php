<?php


namespace Shallowman\Laralog\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Shallowman\Laralog\LaraLogger;

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
     * @param  \Illuminate\Http\Request                                             $request
     * @param  \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response $response
     * write application log when response to the request client
     */
    public function terminate($request, $response)
    {
        $this->setRequestLifecycleVariables($request, $response);
        $this->log->info(null, $this->captureLifecycle());
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

    private function setTimestamp(string $timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function setParameters(string $parameters)
    {
        $this->parameters = $parameters;
    }

    public function setExtra(string $extra = '')
    {
        $this->extra = $extra;
    }

    public function setPlatform(string $platform = '')
    {
        $this->platform = $platform;
    }

    public function setVersion(string $version = '')
    {
        $this->version = $version;
    }

    public function setOs(string $os = '')
    {
        $this->os = $os;
    }

    public function setTag(string $tag = '')
    {
        $this->tag = $tag;
    }

    protected function setAppName(string $app)
    {
        $this->app = $app;
    }

    protected function setEnv(string $env)
    {
        $this->env = $env;
    }

    protected function setChannel(string $channel = 'app')
    {
        $this->channel = $channel;
    }

    protected function setLevel(string $level = 'INFO')
    {
        $this->level = $level;
    }

    protected function setIp(string $ip)
    {
        $this->ip = $ip;
    }

    protected function setUri(string $uri)
    {
        $this->uri = $uri;
    }

    protected function setMethod(string $method)
    {
        $this->method = $method;
    }

    protected function setStart(string $start)
    {
        $this->start = $start;
    }

    protected function setEnd(string $end)
    {
        $this->end = $end;
    }

    protected function setResponse(string $response)
    {
        $this->response = $response;
    }

    protected function setLogChannel(string $logChannel = 'middleware')
    {
        $this->logChannel = $logChannel;
    }

    protected function setPerformance(float $performance)
    {
        $this->performance = $performance;
    }

    public function setMessage(string $message = '')
    {
        $this->msg = $message;
    }

    public function setRequestLifecycleVariables(Request $request, Response $response)
    {
        $this->setAppName(config('app.name') ?? 'Laravel');
        $this->setChannel();
        $this->setEnv(config('app.env') ?? 'Unknown');
        $this->setLogChannel();
        $this->setLevel();
        $this->setOs();
        $this->setPlatform();
        $this->setTag();
        $this->setUri($request->getUri());
        $this->setMethod($request->getMethod());
        $this->setIp(implode(',', $request->getClientIps()));
        $this->setVersion();
        $this->setParameters(collect($request->input())->except(app('config')['laralog.except'])->toJson());
        $this->setStart(Carbon::createFromTimestampMs($this->getStartMicroTimestamp($request) * 1000)->format('Y-m-d H:i:s.u'));
        $this->setEnd(now()->format('Y-m-d H:i.s.u'));
        $this->setPerformance(round(microtime(true) - $this->getStartMicroTimestamp($request), 6));
        $this->setResponse($response->content());
        $this->setMessage();
        $this->setTimestamp(now()->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z'));
        $this->setExtra();
    }

    /**
     * Capture request lifecycle content
     *
     * @return array
     */
    public function captureLifecycle()
    {
        return [
            '@timestamp'  => $this->timestamp,
            'app'         => $this->app,
            'env'         => $this->env,
            'channel'     => $this->channel,
            'logChannel'  => $this->logChannel,
            'uri'         => $this->uri,
            'method'      => $this->method,
            'ip'          => $this->ip,
            'platform'    => $this->platform,
            'version'     => $this->version,
            'os'          => $this->os,
            'level'       => $this->level,
            'tag'         => $this->tag,
            'start'       => $this->start,
            'end'         => $this->end,
            'parameters'  => $this->parameters,
            'performance' => $this->performance,
            'msg'         => $this->msg,
            'response'    => $this->response,
            'extra'       => $this->extra,
        ];
    }
}