<?php

namespace App\Utils;

use DOMDocument;
use DOMElement;
use DOMXPath;

readonly class HTMLProcessor
{
    private DOMXPath $xpath;

    public function __construct(
        string              $html,
        private DOMDocument $dom = new DOMDocument(),
    )
    {
        @$this->dom->loadHTML(
            mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $this->xpath = new DOMXPath($this->dom);
    }

    public function getElementByXPath(string $xpathExpression): ?DOMElement
    {
        $elements = $this->xpath->query($xpathExpression);

        return $elements->length > 0 ? $elements->item(0) : null;
    }


    public function getHTML(): string
    {
        return $this->dom->saveHTML();
    }

    public function removeElementsByClassName(string $className): void
    {
        $elements = $this->xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");

        foreach ($elements as $element) {
            $element->parentNode->removeChild($element);
        }
    }

    public function addClassToElementsByClassName(string $className, string $newClassName): void
    {
        $elements = $this->xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");

        foreach ($elements as $element) {
            $element->setAttribute('class', $element->getAttribute('class') . ' ' . $newClassName);
        }
    }

    public function getElement(string $selector): DOMElement
    {
        $elements = $this->xpath->query($selector);

        return $elements->item(0);
    }

    public function getScriptElementsBySrc(string $srcStartsWith): array
    {
        $elements = $this->xpath->query("//script[starts-with(@src, '$srcStartsWith')]");

        $result = [];

        foreach ($elements as $element) {
            $result[] = $element;
        }

        return $result;
    }
}

