<?php

namespace App\Serializers;

use Exception;
use SimpleXMLElement;

/**
 * TODO: Refactor this class to use the new XML parser DOMDocument
 * This class is used to serialize data coming from orbeon_form_data table and orbeon_form_data_attach table
 * We use it to properly display form submissions on the frontend and to further process the data e.g. for exporting
 */
class OFFormSerializer
{
    /**
     * This method returns all form names that the given form is referencing through select controls.
     *
     * @param string $xmlString
     * @return array
     * @throws Exception
     */
    static function fromXmlDefinitionToRelatedForms(string $xmlString, string $app): array
    {
        $relatedForms = [];
        $xml = OFFormSerializer::createSimpleXMLInstance($xmlString);
        $nodes = $xml->xpath('//*[@resource]');

        foreach ($nodes as $node) {
            $resource = (string)$node['resource'];

            if (str_starts_with($resource, 'http://' . config("app.service_url") . '/api/of/data/' . $app)) {
                $resourcePath = str_replace('http://' . config("app.service_url") . '/api/of/data/' . $app, '', $resource);
                $path = explode('/', $resourcePath, 2);

                $relatedForms[] = [
                    'controlName' => (string)$node['id'],
                    'form' => $path[1],
                ];
            }
        }

        return $relatedForms;
    }

    /**
     * Serializes XML form definition to JSON
     *
     * @throws Exception
     */
    static function fromXmlDefinitionToJsonControls(string $xmlString, bool $withSections = false): array
    {
        $resources = OFFormSerializer::createSimpleXMLInstance($xmlString)->xpath('//resource');

        if (count($resources) === 0) {
            throw new Exception('No resources found in XML');
        }

        $resources = $resources[0];

        if ($withSections) {
            $sectionDefinitions = OFFormSerializer::createSimpleXMLInstance($xmlString)->xpath('//form');

            if (count($sectionDefinitions) === 0) {
                throw new Exception('No form definition found in XML');
            }

            $sectionDefinitions = $sectionDefinitions[0];

            foreach ($sectionDefinitions->children() as $section) {
                foreach ($section->children() as $index => $grid) {
                    if (str_starts_with($grid->getName(), 'grid-')) {
                        foreach ($grid->children() as $control) {
                            $section->addChild($control->getName(), (string)$control);
                        }
                    }
                }
            }
        }

        $result = [];

        foreach ($resources as $control) {
            $controlId = (string)$control->getName();
            if(str_starts_with($controlId, 'section')) {
                continue;
            }
            $label = (string)$control->label;

            $section = null;
            if ($withSections) {
                foreach ($sectionDefinitions->children() as $potentialSection) {
                    foreach ($potentialSection->children() as $child) {
                        if ($child->getName() === $controlId) {
                            $section = $potentialSection;
                            break 2;
                        }
                    }
                }
            }

            $append = &$result;
            if ($section && $withSections) {
                $sectionName = (string)$section->getName();
                if (!isset($result[$sectionName]) || !is_array($result[$sectionName])) {
                    $result[$sectionName] = [];
                }
                $append = &$result[$sectionName];
            }

            if (!empty($label)) {
                $append[$controlId] = $label;
            }
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    private static function createSimpleXMLInstance(string $xmlString): SimpleXMLElement
    {
        libxml_use_internal_errors(true);
        try {
            $xml = new SimpleXMLElement($xmlString);
        } catch (Exception $e) {
            $errors = libxml_get_errors();
            $errorMessage = 'Error while loading XML: ' . $e->getMessage();
            foreach ($errors as $error) {
                $errorMessage .= "\n" . trim($error->message);
            }
            libxml_clear_errors();
            throw new Exception($errorMessage);
        }
        return $xml;
    }

    /**
     * Serializes XML form data to JSON
     *
     * @throws Exception
     */
    static function fromXmlToJsonData(string $xmlString): array
    {
        $result = [];

        OFFormSerializer::processXmlDataNode(
            OFFormSerializer::createSimpleXMLInstance($xmlString),
            $result
        );

        return $result;
    }

    private static function processXmlDataNode($node, &$result): void
    {
        foreach ($node as $key => $value) {
            if ($value->count() > 0) {
                OFFormSerializer::processXmlDataNode($value, $result);
            } else {
                $result[(string)$key] = (string)$value;
            }
        }
    }

    /**
     * Used to serialize form data to XML to be saved in the orbeon_form_data table
     *
     * @return void
     */
    static function fromArrayToXmlSubmission(array $data): string
    {
        $xml = new SimpleXMLElement('
          <form xmlns:fr="http://orbeon.org/oxf/xml/form-runner" fr:data-format-version="4.0.0"></form>
        ');

        foreach ($data as $sectionKey => $controls) {
            $section = $xml->addChild($sectionKey);

            foreach ($controls as $controlKey => $value) {
                $section->addChild($controlKey, htmlspecialchars($value));
            }
        }

        return $xml->asXML();
    }


    /**
     * Serializes JSON form data with added definition to quickly map the data with controls to XML
     * It's used to export form data to XML for Orbeon to use it in dynamic dropdowns
     *
     * @param array $json
     * @return bool|string
     */
    static function fromArrayToXmlDynamicDropdownData(array $json): bool|string
    {
        $xmlData = new SimpleXMLElement('<?xml version="1.0"?><data></data>');

        self::arrayToXmlDynamicDropdownData($json, $xmlData);

        return $xmlData->asXML();
    }

    /**
     * Helper function to convert array to XML
     */
    private static function arrayToXmlDynamicDropdownData($data, $xml_data): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml_data->addChild("item");
                self::arrayToXmlDynamicDropdownData($value, $subnode);
            } else {
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}
