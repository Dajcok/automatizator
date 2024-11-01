<?php

namespace App\Serializers;

use Exception;
use SimpleXMLElement;

/**
 * This class is used to serialize data coming from orbeon_form_data table and orbeon_form_data_attach table
 * We use it to properly display form submissions on the frontend and to further process the data e.g. for exporting
 */
class OFFormSerializer
{
    /**
     * Serializes XML form definition to JSON
     *
     * @throws Exception
     */
    static function fromXmlToJsonControls(string $xmlString): array
    {
        $resources = OFFormSerializer::createSimpleXMLInstance($xmlString)->xpath('//resource');

        $result = [];

        foreach ($resources as $resource) {
            foreach ($resource as $control) {
                $controlId = (string)$control->getName();
                $label = (string)$control->label;

                if (!empty($label)) {
                    $result[$controlId] = $label;
                }
            }
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    private static function createSimpleXMLInstance(string $xmlString): SimpleXMLElement
    {
        try {
            return new SimpleXMLElement($xmlString);
        } catch (Exception $e) {
            throw new Exception('Error while loading XML: ' . $e->getMessage());
        }
    }

    /**
     * Serializes XML form data to JSON
     *
     * @throws Exception
     */
    static function fromXmlToJsonData(string $xmlString): array
    {
        $result = [];

        OFFormSerializer::processNode(
            OFFormSerializer::createSimpleXMLInstance($xmlString),
            $result
        );

        return $result;
    }

    private static function processNode($node, &$result): void
    {
        foreach ($node as $key => $value) {
            if ($value->count() > 0) {
                OFFormSerializer::processNode($value, $result);
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
