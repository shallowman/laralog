<?php

namespace Shallowman\Laralog\Tests\Unit;

use Shallowman\Laralog\Http\Middleware\CaptureRequestLifecycle;
use Shallowman\Laralog\Tests\BaseTestCase;

class CaptureRequestLifecycleTest extends BaseTestCase
{
    public function testResponseToString()
    {
        $obj = new \stdClass();
        $this->assertSame(var_export($obj, true), CaptureRequestLifecycle::responseToString($obj));
        $array = [1];
        $this->assertSame(var_export($array, true), CaptureRequestLifecycle::responseToString($array));
        $bool = false;
        $this->assertSame(var_export($bool, true), CaptureRequestLifecycle::responseToString($bool));
        $int = 1222;
        $this->assertSame(var_export($int, true), CaptureRequestLifecycle::responseToString($int));
        $float = 1222.333;
        $this->assertSame(var_export($float, true), CaptureRequestLifecycle::responseToString($float));
        $string = 'test-case1';
        $this->assertSame($string, CaptureRequestLifecycle::responseToString($string));
    }
}
