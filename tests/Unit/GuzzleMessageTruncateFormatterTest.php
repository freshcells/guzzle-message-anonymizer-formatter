<?php

namespace Freshcells\Tests\GuzzleMessageAnonymizerFormatter\Unit;

use Freshcells\GuzzleMessageAnonymizerFormatter\AbstractAnonymizerFormatter;
use Freshcells\GuzzleMessageAnonymizerFormatter\GuzzleMessageTruncatePayloadFormatter;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class GuzzleMessageTruncateFormatterTest extends TestCase
{
    public function testFormat()
    {
        $string = file_get_contents(__DIR__.'/../Fixtures/example.xml');

        $formatter  = new GuzzleMessageTruncatePayloadFormatter(200, AbstractAnonymizerFormatter::DEBUG);
        $request    = new Request('GET', 'http://test', [], $string);
        $res        = $formatter->format($request);
        $this->assertEquals(233, strlen($res));
    }
}
