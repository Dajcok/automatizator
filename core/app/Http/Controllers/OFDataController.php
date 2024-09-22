<?php

namespace App\Http\Controllers;

use App\Http\Resources\BaseCollection;
use App\Http\Resources\OFDataResource;
use App\Models\OrbeonFormDefinition;
use App\Repositories\OFDataRepository;
use App\Repositories\OFDefinitionRepository;
use App\Serializers\OFFormSerializer;
use App\Services\OrbeonServiceContract;
use App\Utils\VerboseToKey;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function PHPUnit\Framework\isEmpty;

/**
 * Class OFDataController
 * This controller is used to manage suubmissions of Orbeon forms.
 */
class OFDataController extends ResourceController
{
    public function __construct(
        OFDataRepository                        $repository,
        OFDataResource                          $resource,
        BaseCollection                          $collection,
        private readonly OrbeonServiceContract  $service,
        private readonly OFDefinitionRepository $formDefinitionRepository
    )
    {
        parent::__construct($repository, $resource, $collection, OrbeonFormDefinition::class);
    }

    public function save(string $app, string $form, string $document, string $data, bool $final = true): Application|Response|ResponseFactory
    {
        $bin = $this->service->saveFormData($app, $form, $document, $data, $final);

        return response($bin)
            ->header('Content-Type', 'application/zip')
            ->header('Content-Disposition', 'inline');
    }

    /**
     * Gets all submissions for a given app and form.
     *
     * @throws Exception
     */
    public function index(Request $request): JsonResponse
    {
        [$app, $form] = [$request->route("app"), $request->route("form")];
        //Used to query both orbeon_form_data and orbeon_form_definition tables
        $where = [
            "app" => $app,
            "form" => $form
        ];

        $definition = $this->formDefinitionRepository->query($where)[0];

        if(!$definition) {
            return response()->json([]);
        }

        $serializedDefinition = OFFormSerializer::serializeControls($definition->xml);

        $results = $this->repository->query($where);

        if(!$results) {
            return response()->json([]);
        }

        $serialized = [];

        foreach ($results as $result) {
            $res = OFFormSerializer::serialize($result->xml);

            if (!$res) {
                continue;
            }

            //Iterate over the serialized definition and replace control ids with their labels
            foreach (array_keys($res) as $key) {
                if (array_key_exists($key, $serializedDefinition)) {
                    $res[VerboseToKey::convert($serializedDefinition[$key])] = $res[$key];
                    unset($res[$key]);
                }
            }

            $res["id"] = $result->id;
            $serialized[] = $res;
        }

        return response()->json($serialized);
    }
}
