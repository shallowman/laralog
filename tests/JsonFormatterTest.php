<?php

namespace Shallowman\Laralog\Tests;

use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Shallowman\Laralog\Formatter\JsonFormatter;

class JsonFormatterTest extends TestCase
{
    public function testGetStartMicroTimestampWithSystemTs()
    {
        app()->bind('request', function () {
            return Request::createFromGlobals();
        });

        $this->assertEqualsWithDelta(microtime(true), JsonFormatter::getStartMicroTimestamp(), 500.0);
    }

    public function testGetStartMicroTimestampWithRequestGlobalVars()
    {
        app()->bind('request', function () {
            $_SERVER['REQUEST_TIME_FLOAT'] = 1234.56;

            return Request::createFromGlobals();
        });
        $this->assertSame(1234.56, JsonFormatter::getStartMicroTimestamp());
    }

    public function testGetStartMicroTimestampWithDefinedConst()
    {
        define('LARAVEL_START', 123.1);
        $this->assertSame(123.1, JsonFormatter::getStartMicroTimestamp());
    }

    public function testNormalizeExtra()
    {
        $context = [
            'exception' => new \Exception('extra-exception'),
            'redundancy' => 'redundancy',
            'redundancy_again' => [
                'test',
                new \stdClass(),
            ],
        ];
        $formatter = new JsonFormatter();
        $this->assertArrayNotHasKey('exception', json_decode($formatter->normalizeExtra($context), true));
        $this->assertArrayHasKey('redundancy', json_decode($formatter->normalizeExtra($context), true));
        $this->assertArrayHasKey('redundancy_again', json_decode($formatter->normalizeExtra($context), true));
    }

    public function testTailor()
    {
        $normalizeRecord = ['a' => 'a', 'b' => 'b', 'c' => 'c'];
        $context = ['1', '2', 'a' => '3', 'd' => 'd'];
        $formatter = new JsonFormatter();
        $this->assertSame(['a' => '3', 'b' => 'b', 'c' => 'c'], $formatter->tailor($normalizeRecord, $context));
    }
}
