<?php

namespace Freshcells\Tests\GuzzleMessageAnonymizerFormatter\Unit;

use Freshcells\GuzzleMessageAnonymizerFormatter\AbstractAnonymizerFormatter;
use GuzzleHttp\Psr7\Request;
use Freshcells\GuzzleMessageAnonymizerFormatter\GuzzleMessageJsonAnonymizerFormatter;
use PHPUnit\Framework\TestCase;

class GuzzleMessageJsonAnonymizerFormatterTest extends TestCase
{
    public function testFormat()
    {
        $string = file_get_contents(__DIR__.'/../Fixtures/example.json');

        $props               = ['CardHolderGivenName', 'CardHolderSurname', 'CVC2', 'CardNumber'];
        $substitute          = '*****';
        $headersToSubstitute = [
            'Authorization' => $substitute,
        ];
        $truncateElements    = ['VeryLongText' => 25];
        $formatter           = new GuzzleMessageJsonAnonymizerFormatter(
            $props,
            $substitute,
            AbstractAnonymizerFormatter::DEBUG,
            $headersToSubstitute,
            $truncateElements
        );
        $request             = new Request('GET', 'http://test', ['Authorization' => 'Bearer: pssst'], $string);
        $res                 = $formatter->format($request);
        foreach ($props as $prop) {
            $this->assertTrue(strpos($res, '"'.$prop.'": "'.$substitute.'"') > 0);
        }
        foreach ($props as $prop) {
            $this->assertTrue(strpos($res, '"'.$prop.'": "'.$substitute.'"') > 0);
        }
        $this->assertTrue(strpos($res, 'Powder carrot cake jel...') > 0);
    }
}
