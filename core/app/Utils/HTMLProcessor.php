<?php

namespace App\Utils;

use DOMDocument;
use DOMXPath;

readonly class HTMLProcessor
{
    public function __construct(
        string              $html,
        private DOMDocument $dom = new DOMDocument(),
    )
    {
        @$this->dom->loadHTML(
            mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
    }


    public function getHTML(): string
    {
        return $this->dom->saveHTML();
    }

    public function removeElementsByClassName(string $className): void
    {
        $xpath = new DOMXPath($this->dom);

        $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");

        foreach ($elements as $element) {
            $element->parentNode->removeChild($element);
        }
    }

    public function addClassToElementsByClassName(string $className, string $newClassName): void
    {
        $xpath = new DOMXPath($this->dom);

        $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");

        foreach ($elements as $element) {
            $element->setAttribute('class', $element->getAttribute('class') . ' ' . $newClassName);
        }
    }
}

