<?php
declare(strict_types=1);

namespace Shallowman\Laralog\Tests\Unit;

use Shallowman\Laralog\Http\Middleware\CaptureRequestLifecycle;
use Shallowman\Laralog\Tests\BaseTestCase;

class CaptureRequestLifecycleTest extends BaseTestCase
{
    public function testResponseToString(): void
    {
        $obj = new \stdClass();
        $this->assertSame(var_export($obj, true), CaptureRequestLifecycle::responseToString($obj));
        $this->assertSame(var_export([1, 2], true), CaptureRequestLifecycle::responseToString([1, 2]));
        $this->assertSame(var_export([1.234, 2.2], true), CaptureRequestLifecycle::responseToString([1.234, 2.2]));
        $this->assertSame(var_export([1, 'sting', 2.87], true), CaptureRequestLifecycle::responseToString([1, 'sting', 2.87]));
        $this->assertSame(var_export(false, true), CaptureRequestLifecycle::responseToString(false));
        $this->assertSame(var_export(1222, true), CaptureRequestLifecycle::responseToString(1222));
        $this->assertSame(var_export(1222.333, true), CaptureRequestLifecycle::responseToString(1222.333));
        $this->assertSame('test-case1', CaptureRequestLifecycle::responseToString('test-case1'));
        $this->assertSame(var_export(null, true), CaptureRequestLifecycle::responseToString(null));
    }
}
