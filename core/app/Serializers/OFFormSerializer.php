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
     * Serializes JSON form data with added definition to quickly map the data with controls to XML
     *
     * @param array $json
     * @return bool|string
     */
    static function fromJsonToXmlDataWithControls(array $json): bool|string
    {
        function arrayToXml($data, $xml_data): void
        {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $subnode = $xml_data->addChild("item");
                    arrayToXml($value, $subnode);
                } else {
                    $xml_data->addChild("$key", htmlspecialchars("$value"));
                }
            }
        }

        $xmlData = new SimpleXMLElement('<?xml version="1.0"?><data></data>');

        arrayToXml($json, $xmlData);

        return $xmlData->asXML();
    }
}
