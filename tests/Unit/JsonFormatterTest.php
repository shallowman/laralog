<?php

namespace Shallowman\Laralog\Tests\Unit;

use Shallowman\Laralog\Formatter\JsonFormatter;
use Shallowman\Laralog\Tests\BaseTestCase;

class JsonFormatterTest extends BaseTestCase
{
    public function testGetStartMicroTimestampWithSystemTs(): void
    {
        $this->assertEqualsWithDelta(microtime(true), JsonFormatter::getStartMicroTimestamp(), 1000.0);
    }

    public function testGetStartMicroTimestamp(): void
    {
        define('LARAVEL_START', microtime(true));
        $this->assertSame(LARAVEL_START, JsonFormatter::getStartMicroTimestamp());
    }

    public function testNormalizeExtra(): void
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

    public function testTailor(): void
    {
        $normalizeRecord = ['a' => 'a', 'b' => 'b', 'c' => 'c'];
        $context = ['1', '2', 'a' => '3', 'd' => 'd'];
        $formatter = new JsonFormatter();
        $this->assertSame(['a' => '3', 'b' => 'b', 'c' => 'c'], $formatter->tailor($normalizeRecord, $context));
    }
}
