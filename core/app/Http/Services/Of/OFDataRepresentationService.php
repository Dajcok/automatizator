<?php

namespace App\Http\Services\Of;

use App\Models\Of\OrbeonFormData;
use App\Repositories\Of\OFDataRepository;
use App\Http\Controllers\Of\OFDefinitionController;
use App\Repositories\Of\OFDefinitionRepository;
use App\Repositories\Of\OrbeonIControlTextRepository;
use App\Serializers\OFFormSerializer;
use App\Utils\LabelToKey;


readonly class OFDataRepresentationService
{
    public function __construct(
        private OFDataRepository             $ofDataRepository,
        private OFDefinitionRepository       $ofDefinitionRepository,
        private OrbeonIControlTextRepository $controlTextRepository,
    )
    {
    }

    public function toFormBuilderDataRepresentation(
        string $appName
    ): array
    {
        $data = $this->ofDataRepository->query([
            "app" => "orbeon",
            "form" => "builder",
        ]);

        return $data->filter(function ($item) use ($appName) {
            $formMeta = $this->controlTextRepository->getFormMeta($item->id);

            //Skip if app_name is not same as parent app
            if (!$formMeta || empty($formMeta["app_name"]) || $formMeta["app_name"] !== $appName) {
                return false;
            }

            // Skip if form_name or form_title is missing
            if (empty($formMeta["form_name"]) || empty($formMeta["form_title"])) {
                return false;
            }

            $item->form_name = $formMeta["form_name"];
            $item->form_title = $formMeta["form_title"];
            $item->is_draft = !$this->ofDefinitionRepository->exists([
                "app" => $appName,
                "form" => $item->form_name,
            ]);

            return true;
        });
    }

    public function toFormDataRepresentation(
        OrbeonFormData $data,
        string $appName,
        string $formName,
        $verbose = false
    ): \Illuminate\Http\JsonResponse|array
    {
        $res = OFFormSerializer::fromXmlToJsonData($data->xml);

        if (!$res) {
            return response()->json([
                "message" => "Submission not found"
            ], 404);
        }

        if ($verbose) {
            $definition = $this->ofDefinitionRepository->queryAndReturnNewest([
                "app" => $appName,
                "form" => $formName,
            ]);

            if (count($definition) === 0) {
                return response()->json([
                    "message" => "Form definition not found"
                ], 404);
            }

            $definition = $definition[0];

            $serializedDefinition = OFFormSerializer::fromXmlToJsonControls($definition->xml);

            foreach (array_keys($res) as $key) {
                if (!array_key_exists($key, $serializedDefinition)) {
                    continue;
                }

                $res[LabelToKey::convert($serializedDefinition[$key])] = $res[$key];
                unset($res[$key]);
            }
        }

        $res["id"] = $data->id;
        $res["document_id"] = $data->document_id;
        $res["updated_at"] = $data->last_modified_time;
        $res["created_at"] = $data->created;

        return $res;
    }
}
