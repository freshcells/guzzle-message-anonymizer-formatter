<?php

namespace Freshcells\Tests\GuzzleMessageAnonymizerFormatter\Unit;

use Freshcells\GuzzleMessageAnonymizerFormatter\AbstractAnonymizerFormatter;
use GuzzleHttp\Psr7\Request;
use Freshcells\GuzzleMessageAnonymizerFormatter\GuzzleMessageXmlAnonymizerFormatter;
use PHPUnit\Framework\TestCase;

class GuzzleMessageXmlAnonymizerFormatterTest extends TestCase
{
    public function testFormat()
    {
        $string = file_get_contents(__DIR__.'/../Fixtures/example.xml');

        $namespaces = ['none' => 'http://namespace.de/middleware/payment/'];
        $tags       = ['none:CardHolderGivenName', 'none:CardHolderSurname'];
        $attributes = ['none:PaymentCard[@CVC2]', 'none:PaymentCard[@CardNumber]'];
        $substitute = '*****';
        $formatter  = new GuzzleMessageXMLAnonymizerFormatter(
            $tags,
            $attributes,
            $substitute,
            AbstractAnonymizerFormatter::DEBUG,
            [],
            $namespaces
        );
        $request    = new Request('GET', 'http://test', [], $string);
        $res        = $formatter->format($request);
        foreach ($tags as $tag) {
            $match      = [];
            $tag = str_replace('none:', '', $tag);
            preg_match('/\<'.$tag.'\>(.*?)\<\/'.$tag.'\>/', $res, $match);
            $this->assertEquals($substitute, $match[1]);
        }
        foreach (['CVC2', 'CardNumber'] as $attribute) {
            $match = [];
            preg_match('/ '.$attribute.'="(.*?)"/', $res, $match);
            $this->assertEquals($substitute, $match[1]);
        }
    }
}
