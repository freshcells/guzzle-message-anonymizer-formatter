<?php

namespace Freshcells\GuzzleMessageAnonymizerFormatter;

class GuzzleMessageJsonAnonymizerFormatter extends AbstractAnonymizerFormatter
{
    public function __construct(
        array $elements,
        string $substitute = '*****',
        string $template = self::DEBUG,
        array $headersToSubstitute = [],
        array $truncateElements = []
    )
    {
        parent::__construct($elements, [], $substitute, $template, $headersToSubstitute, $truncateElements);
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

        foreach ($this->truncateElements as $truncateElement => $maxLength) {
            $content = preg_replace_callback(
                '/\"'.$truncateElement.'\"\:(.*?)\"(.*?)\"/s',
                function ($hit) use ($truncateElement, $maxLength) {
                    return '"'.$truncateElement.'":'.$hit[1].'"'.$this->truncateElement($hit[2], $maxLength).'"';
                },
                $content
            );
        }

        return $content;
    }
}
