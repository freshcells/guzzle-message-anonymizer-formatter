<?php

namespace Freshcells\GuzzleMessageAnonymizerFormatter;

class GuzzleMessageXmlAnonymizerFormatter extends AbstractAnonymizerFormatter
{
    protected function hidePrivateData(string $content)
    {
        //tags
        foreach ($this->elements as $field) {
            $content = preg_replace(
                sprintf('/<(%s[^>]*)>.*?<\/(%s)>/i', $field, $field),
                sprintf('<%s>%s</%s>', '$1', $this->substitute, '$2'),
                $content
            );
        }
        //attributes
        foreach ($this->attributes as $attribute) {
            $re    = '/ '.$attribute.'="[^"]*/';
            $subst = ' '.$attribute.'="'.$this->substitute;

            $content = preg_replace($re, $subst, $content);
        }

        return $content;
    }
}
