<?php

namespace Ivoba\Tests\GuzzleMessageAnonymizerFormatter\Unit;

use GuzzleHttp\Psr7\Request;
use Ivoba\GuzzleMessageAnonymizerFormatter\GuzzleMessageXmlAnonymizerFormatter;
use PHPUnit\Framework\TestCase;

class GuzzleMessageXmlAnonymizerFormatterTest extends TestCase
{
    public function testFormat()
    {
        $string = file_get_contents(__DIR__.'/../Fixtures/example.xml');

        $tags       = ['CardHolderGivenName', 'CardHolderSurname'];
        $attributes = ['CVC2', 'CardNumber'];
        $substitute = '*****';
        $formatter  = new GuzzleMessageXMLAnonymizerFormatter($tags, $attributes, $substitute);
        $request    = new Request('GET', 'http://test', [], $string);
        $res        = $formatter->format($request);
        foreach ($tags as $tag) {
            $match      = [];
            preg_match('/\<'.$tag.'\>(.*?)\<\/'.$tag.'\>/', $res, $match);
            $this->assertEquals($substitute, $match[1]);
        }
        foreach ($attributes as $attribute) {
            $match = [];
            preg_match('/ '.$attribute.'="(.*?)"/', $res, $match);
            $this->assertEquals($substitute, $match[1]);
        }
    }
}
