<?php

namespace Ivoba\Tests\GuzzleMessageAnonymizerFormatter\Unit;

use GuzzleHttp\Psr7\Request;
use Ivoba\GuzzleMessageAnonymizerFormatter\GuzzleMessageJsonAnonymizerFormatter;
use PHPUnit\Framework\TestCase;

class GuzzleMessageJsonAnonymizerFormatterTest extends TestCase
{
    public function testFormat()
    {
        $string = file_get_contents(__DIR__.'/../Fixtures/example.json');

        $props       = ['CardHolderGivenName', 'CardHolderSurname', 'CVC2', 'CardNumber'];
        $substitute = '*****';
        $formatter  = new GuzzleMessageJsonAnonymizerFormatter($props, $substitute);
        $request    = new Request('GET', 'http://test', [], $string);
        $res        = $formatter->format($request);
        foreach ($props as $prop) {
            $this->assertTrue(strpos($res, '"'.$prop.'": "'.$substitute.'"') > 0);
        }
    }
}
