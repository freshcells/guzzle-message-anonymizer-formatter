<?php

namespace Freshcells\GuzzleMessageAnonymizerFormatter;

class GuzzleMessageJsonAnonymizerFormatter extends AbstractAnonymizerFormatter
{
    public function __construct(
        array $elements,
        string $substitute = '*****',
        string $template = self::DEBUG,
        array $headersToSubstitute = []
    )
    {
        parent::__construct($elements, $attributes = [], $substitute, $template, $headersToSubstitute);
    }

    protected function hidePrivateData(string $content)
    {
        //tags
        foreach ($this->elements as $field) {
            $content = preg_replace(
                '/\"'.$field.'\"\:(.*?)\".*?\"/s',
                '"'.$field.'":$1"'.$this->substitute.'"',
                $content
            );
        }

        return $content;
    }
}
