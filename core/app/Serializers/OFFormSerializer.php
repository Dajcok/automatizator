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
     * @throws Exception
     */
    static function serializeControls(string $xmlString): array
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
     * @throws Exception
     */
    static function serialize(string $xmlString): array
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
}
