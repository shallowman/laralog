<?php

namespace Shallowman\Laralog\Tests;

use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use Shallowman\Laralog\Http\Middleware\CaptureRequestLifecycle;

class CaptureRequestLifecycleTest extends TestCase
{
    public function bootstrapLaravelApp()
    {
        app()->bind('request', function () {
            return Request::createFromGlobals();
        });
        app()->bind('config', function () {
            return new Repository();
        });
    }

    public function bootstrapLaravelAppWithLaralogConf()
    {
        app()->bind('request', function () {
            return Request::createFromGlobals();
        });
        app()->bind('config', function () {
            $c = require __DIR__.'/../config/laralog.php';
            $c = ['laralog' => $c];

            return new Repository($c);
        });
    }

    public function testClipLog()
    {
        $this->bootstrapLaravelApp();
        $log = Str::random(1004);
        $clippedLog = mb_substr($log, 0, CaptureRequestLifecycle::DEFAULT_CLIPPED_LENGTH).'...clipped';
        $this->assertSame($clippedLog, CaptureRequestLifecycle::clipLog($log));
    }

    public function testLabel()
    {
        $this->bootstrapLaravelAppWithLaralogConf();
        $this->assertSame('', CaptureRequestLifecycle::label());
        $this->assertEmpty(CaptureRequestLifecycle::label());
        putenv('LARALOG_CLIPPED_LENGTH=1000');
        $this->assertSame('clipped', CaptureRequestLifecycle::label());
    }
}
