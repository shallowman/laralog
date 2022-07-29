<?php

namespace Shallowman\Laralog\Tests\Feature;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Monolog\Handler\TestHandler;
use Shallowman\Laralog\Tests\BaseTestCase;

class MiddlewareTest extends BaseTestCase
{
    public const CONTEXT_KEYS = [
        '@timestamp',
        'app',
        'env',
        'level',
        'logChannel',
        'channel',
        'uri',
        'method',
        'ip',
        'platform',
        'version',
        'os',
        'start',
        'end',
        'parameters',
        'performance',
        'response',
        'extra',
        'msg',
        'headers',
        'hostname',
        'tag',
    ];

    public function testRequestCase1()
    {
        $response = $this->get('test/case1');
        $response->assertStatus(200);
    }

    public function testRequestCase2()
    {
        try {
            $response = $this->get('test/case2');
            $response->assertStatus(200);
        } catch (\Throwable $e) {
            $this->expectErrorMessage('error caused by middleware');
        }
    }

    public function testCaptureHttpLifecycleLog()
    {
        try {
            $response = $this->get('test/case2');
            $response->assertStatus(200);
            $respContent = $response->getContent();
            $this->assertJson($respContent);
            $log = $this->getLaraLogger();
            $handlers = $log->channel('laralog')->getHandlers();
            $this->assertNotEmpty($handlers);
            $this->assertArrayHasKey(0, $handlers);
            $handler = $handlers[0];
            $this->assertInstanceOf(TestHandler::class, $handler, 'not load test log handler');
            $handler->setSkipReset(true);
            $handler->hasInfoRecords();
            $records = $handler->getRecords();
            $this->assertArrayHasKey(0, $records);
            $this->assertArrayHasKey('context', $records[0]);
            $context = $records[0]['context'];
            $this->assertIsArray($context, 'context not write to log');
            foreach (self::CONTEXT_KEYS as $k) {
                $this->assertArrayHasKey($k, $context);
            }
            $this->assertEquals((int) config('laralog.log_clipped_length') + mb_strlen('...clipped'), mb_strlen($context['response']));
            $this->assertSame('clipped', $context['tag']);
        } catch (\Throwable $e) {
            $this->expectErrorMessage($e->getMessage());
        }
    }

    public function testLogWrite()
    {
        try {
            $testCase1Context = ['test' => Str::random(100)];
            $testCase2Context = ['error' => Str::random(1077)];
            Log::info('test-info', $testCase1Context);
            $logger = $this->app->get('log');
            $handlers = $logger->channel('laralog')->getHandlers();
            $handler = $handlers[0];
            $this->assertInstanceOf(TestHandler::class, $handler, 'not load test log handler');
            $handler->setSkipReset(true);
            $handler->hasInfoRecords();
            $handler->hasInfoThatContains('test-info');
            $records = $handler->getRecords();
            $this->assertArrayHasKey(0, $records);
            $this->assertArrayHasKey('context', $records[0]);
            $context = $records[0]['context'];
            $this->assertIsArray($context, 'context not write to log');
            $this->assertArrayHasKey('test', $context);
            $this->assertEquals($testCase1Context, $context);
            $this->assertArrayHasKey('formatted', $records[0]);
            $formatMsg = $records[0]['formatted'];
            $this->assertJson($formatMsg);
            $formattedMsg = json_decode($formatMsg, true);
            $this->assertIsArray($formattedMsg);
            $this->assertArrayHasKey('tag', $formattedMsg);
            $this->assertSame('', $formattedMsg['tag']);
            $this->assertEquals(mb_strlen(json_encode($testCase1Context)), mb_strlen($formattedMsg['extra']));
            Log::error('test-error', $testCase2Context);
            $records = $handler->getRecords();
            $this->assertArrayHasKey(1, $records);
            $record = $records[1];
            $this->assertArrayHasKey('context', $record);
            $context = $record['context'];
            $this->assertIsArray($context, 'context not write to log');
            $this->assertArrayHasKey('error', $context);
            $this->assertEquals($testCase2Context, $context);
            $this->assertArrayHasKey('formatted', $record);
            $this->assertEquals($testCase2Context, $context);
            $this->assertArrayHasKey('formatted', $record);
            $formatMsg = $record['formatted'];
            $this->assertJson($formatMsg);
            $formattedMsg = json_decode($formatMsg, true);
            $this->assertIsArray($formattedMsg);
            $this->assertArrayHasKey('tag', $formattedMsg);
            $this->assertSame('clipped', $formattedMsg['tag']);
            $this->assertEquals((int) config('laralog.log_clipped_length') + mb_strlen('...clipped'), mb_strlen($formattedMsg['extra']));
        } catch (\Throwable $e) {
            $this->expectErrorMessage($e->getMessage());
        }
    }
}
