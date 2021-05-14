<?php

namespace Freshcells\GuzzleMessageAnonymizerFormatter;

class GuzzleMessageXmlAnonymizerFormatter extends AbstractAnonymizerFormatter
{
    protected $namespaces = [];

    public function __construct(
        array $elements,
        array $attributes = [],
        string $substitute = '*****',
        string $template = self::DEBUG,
        array $headersToSubstitute = [],
        array $namespaces = []
    ) {
        $this->namespaces = $namespaces;
        parent::__construct($elements, $attributes, $substitute, $template, $headersToSubstitute);
    }

    protected function hidePrivateData(string $content)
    {
        try {
            $doc = new \DOMDocument();
            $doc->loadXML($content);
            $xpath = new \DOMXPath($doc);
            foreach ($this->namespaces as $namespace => $uri) {
                $xpath->registerNamespace($namespace, $uri);
            }

            foreach ($this->elements as $field) {
                $query   = '//'.$field.'/text()';
                $entries = $xpath->query($query);
                foreach ($entries as $entry) {
                    $entry->data = $this->substitute;
                }
            }

            foreach ($this->attributes as $attribute) {
                $entries = $xpath->query('//'.$attribute);
                foreach ($entries as $entry) {
                    foreach ($entry->attributes as $attribute) {
                        $attribute->value = $this->substitute;
                    }
                }
            }

            return $doc->saveXml();
        } catch (\Exception $e) {
            // noop
        }

        return $content;
    }
}
