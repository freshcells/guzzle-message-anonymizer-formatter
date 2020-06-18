<?php

namespace Freshcells\Tests\GuzzleMessageAnonymizerFormatter\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Freshcells\GuzzleMessageAnonymizerFormatter\GuzzleMessageJsonAnonymizerFormatter;
use PHPUnit\Framework\TestCase;
use WMDE\PsrLogTestDoubles\LoggerSpy;

class GuzzleMessageJsonAnonymizerFormatterTest extends TestCase
{
    public function testFormat()
    {
        $handler = new CurlHandler();
        $stack   = HandlerStack::create($handler);
        $formatter = new GuzzleMessageJsonAnonymizerFormatter(['X-Foo']);
        $loggerSpy = new LoggerSpy();
        $loggerMiddleware = Middleware::log($loggerSpy, $formatter);
        $stack->push($loggerMiddleware);
        $config  = [
            'base_uri' => 'http://httpbin.org',
            'timeout'  => 2.0,
            'handler'  => $stack,
        ];

        $client  = new Client($config);
        $client->post(
            '/post',
            [
                'headers' => [
                    'User-Agent' => 'ivoba/guzzle-message-anonymizer-formatter',
                    'Accept'     => 'application/json',
                    'X-Foo'      => ['Bar', 'Baz'],
                ]
            ]);
        $this->assertTrue(strpos($loggerSpy->getFirstLogCall()->getMessage(), '"X-Foo": "*****"') > 0);
    }
}
