<?php


namespace Shallowman\Laralog\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use stdClass;

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
     * standardize response content data structure
     *
     * @param $response string response content
     *
     * @return mixed|stdClass
     */
    protected function standardizeResponse($response)
    {
        if (!$response) {
            return new stdClass();
        }

        $response = @json_decode($response, true);

        if (is_array($response) && array_key_exists('data', $response)) {
            $response['data'] = json_encode($response['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $response;
    }

    /**
     * @param  \Illuminate\Http\Request                   $request
     * @param  \Symfony\Component\HttpFoundation\Response $response
     * write application log when response to the request client
     */
    public function terminate($request, $response)
    {
        $level = $request->attributes->get('log_level') ?: 'info';
        $context = $request->attributes->get('context');
        $message = $request->attributes->get('message');
        $response = $this->standardizeResponse($response->getContent());
        $now = Carbon::now();
        $content = [
            'app'         => config('app.name'),
            'uri'         => $request->getHost().$request->getRequestUri(),
            'method'      => $request->method(),
            'ip'          => implode('|', $request->getClientIps()),
            'platform'    => '',
            'version'     => '',
            'os'          => '',
            'level'       => $level,
            'tag'         => '',
            'start'       => Carbon::createFromTimestampMs(round(LARAVEL_START * 1000))->format('Y-m-d H:i:s.u'),
            'end'         => $now->format('Y-m-d H:i:s.u'),
            'parameters'  => json_encode($request->input(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'performance' => round(microtime(true) - LARAVEL_START, 3),
            'details'     => [
                'message' => $message,
                'detail'  => $context,
            ],
            'response'    => $response,
        ];

        $timestamp = substr($now->setTimezone('UTC')->format('Y-m-d\TH:i:s.u'), 0, -3).'Z';
        $this->service->channel('filebeat')->log($level, '', ['api' => $content, '@timestamp' => $timestamp]);
    }

    private function packReqContentsToLog()
    {

    }

    private function getRequestStart(Request $request)
    {
        return defined('LARAVEL_START') ? LARAVEL_START : $request->server('REQUEST_TIME');
    }

    private function getRequestEnd()
    {
        return now()->format('Y-m-d H:i:s.u');
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

    protected function setLevel(string $level = 'info')
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

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function captureRequestLifecycleParameters(Request $request, Response $response)
    {
    }

    public function packRequestLifecycleContents()
    {

    }
}