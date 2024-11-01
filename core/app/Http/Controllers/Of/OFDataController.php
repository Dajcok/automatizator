<?php

namespace App\Http\Controllers\Of;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\BaseCollection;
use App\Http\Resources\Of\OFBuilderDataCollection;
use App\Http\Resources\Of\OFBuilderDataResource;
use App\Http\Resources\Of\OFDataCollection;
use App\Http\Resources\Of\OFDataResource;
use App\Models\Of\OrbeonFormData;
use App\Repositories\Core\ModelConfigRepository;
use App\Repositories\Of\OFDataRepository;
use App\Repositories\Of\OFDefinitionRepository;
use App\Repositories\Of\OrbeonIControlTextRepository;
use App\Repositories\Of\OrbeonICurrentRepository;
use App\Serializers\OFFormSerializer;
use App\Utils\LabelToKey;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

//TODO: Refactor this controller

/**
 * Class OFDataController
 * This controller is used to manage suubmissions of Orbeon forms.
 */
class OFDataController extends ResourceController
{
    public function __construct(
        OFDataRepository                              $repository,
        OFDataResource                                $resource,
        BaseCollection                                $collection,
        private readonly OFDefinitionRepository       $formDefinitionRepository,
        private readonly OrbeonIControlTextRepository $controlTextRepository,
        private readonly OrbeonICurrentRepository     $orbeonICurrentRepository,
        private readonly ModelConfigRepository        $modelConfigRepository
    )
    {
        parent::__construct($repository, $resource, $collection, OrbeonFormData::class);
    }

    /**
     * Used to submit a form to Orbeon through our API.
     *
     * @throws Exception
     */
    public function store(Request $request): JsonResponse
    {
        [$app, $form] = [$request->route("app"), $request->route("form")];
        $body = $request->json()->all();

        $definition = $this->formDefinitionRepository->queryAndReturnNewest([
            "app" => $app,
            "form" => $form
        ]);

        if (count($definition) === 0) {
            return response()->json([
                "message" => "Form definition not found"
            ], 404);
        }

        $definition = $definition[0];

        $controlsToLabels = OFFormSerializer::fromXmlToJsonControls($definition->xml);
        $dataWithControls = [];
        $currentSection = null;
        foreach ($controlsToLabels as $key => $field) {
            if (str_contains($key, "section")) {
                $currentSection = $key;
                $dataWithControls[$currentSection] = [];
                continue;
            }

            $dataWithControls[$currentSection][$key] = $body[LabelToKey::convert($field)];
        }

        $submissionReadyXml = OFFormSerializer::fromArrayToXmlSubmission($dataWithControls);

        $documentId = sha1(uniqid(mt_rand(), true));

        DB::transaction(function () use ($app, $form, $body, $submissionReadyXml, $documentId, $definition) {
            $inserted = $this->repository->create([
                "app" => $app,
                "form" => $form,
                "xml" => $submissionReadyXml,
                "document_id" => $documentId,
                "created" => now(),
                "form_version" => $definition->form_version,
                "last_modified_time" => now(),
                "deleted" => "N",
                "draft" => "N",
            ]);

            $this->orbeonICurrentRepository->create([
                "data_id" => $inserted->id,
                "document_id" => $documentId,
                "created" => now(),
                "last_modified_time" => now(),
                "draft" => "N",
                "app" => $app,
                "form" => $form,
                "form_version" => $definition->form_version,
            ]);
        });


        return response()
            ->json([
                "message" => "Submission created"
            ], 201);
    }

    /**
     * Gets all submissions for a given app and form.
     *
     * @throws Exception
     */
    public function index(Request $request): Response|JsonResponse
    {
        [$app, $form] = [$request->route("app"), $request->route("form")];
        //Verbose is used to determine whether we should return control labels(if true) or control ids
        $verbose = $request->get("verbose", false);

        $retrievingFormDefinitionData = false;
        $parentApp = null;
        /**
         * Important:
         *  to secure that when retrieving orbeon builder form data, these data are owned by specified app,
         *  we need to also include app name. So if one wants to retrieve orbeon builder form data, the request
         *  should be like this: /api/of/data/$yourAppName:orbeon/builder.
         *  This way we can have info about the parent app.
         */
        if (str_contains($app, ":")) {
            list($parentApp, $orbeonApp) = explode(":", $app, 2);

            if ($orbeonApp !== "orbeon") {
                throw new Exception("Misformed app name");
            }

            $retrievingFormDefinitionData = $orbeonApp === "orbeon";

            $app = "orbeon";
            $form = "builder";
        }

        //Used to query both orbeon_form_data and orbeon_form_definition tables
        $where = [
            "app" => $app,
            "form" => $form
        ];

        if ($retrievingFormDefinitionData) {
            if (!$parentApp) {
                throw new Exception("Parent app is missing");
            }

            $data = $this->repository->query($where);

            $filteredData = $data->filter(function ($item) use ($parentApp) {
                $formMeta = $this->controlTextRepository->getFormMeta($item->id);

                //Skip if app_name is not same as parent app
                if (!$formMeta || empty($formMeta["app_name"]) || $formMeta["app_name"] !== $parentApp) {
                    return false;
                }

                // Skip if form_name or form_title is missing
                if (empty($formMeta["form_name"]) || empty($formMeta["form_title"])) {
                    return false;
                }

                $item->form_name = $formMeta["form_name"];
                $item->form_title = $formMeta["form_title"];
                $item->is_draft = !$this->formDefinitionRepository->exists([
                    "app" => $parentApp,
                    "form" => $item->form_name,
                ]);

                return true;
            });

            $resources = OFBuilderDataResource::collection($filteredData);
            return response()->json(new OFBuilderDataCollection($resources));
        }

        //We use internal host to determine whether Orbeon is fetching data from our core api
        $isOrbeonFetching = $request->headers->get("Host") === config("app.internal_host");

        if ($isOrbeonFetching) {
            $verbose = true;
        }

        $definition = $this->formDefinitionRepository->queryAndReturnNewest($where);

        if (count($definition) === 0) {
            return response()->json([]);
        }

        $definition = $definition[0];

        $serializedDefinition = OFFormSerializer::fromXmlToJsonControls($definition->xml);

        $results = $this->repository->queryAndReturnNewestByDocumentId($where);

        if (!count($results)) {
            return response()->json([]);
        }

        $jsonSerialized = [];

        foreach ($results as $result) {
            $res = OFFormSerializer::fromXmlToJsonData($result->xml);

            if (!$res) {
                continue;
            }

            //Iterate over the serialized definition and replace control ids with their labels
            if ($verbose) {
                foreach (array_keys($res) as $key) {
                    if (array_key_exists($key, $serializedDefinition)) {
                        $res[LabelToKey::convert($serializedDefinition[$key])] = $res[$key];
                        unset($res[$key]);
                    }
                }
            }

            $res["id"] = $result->id;
            $res["document_id"] = $result->document_id;
            $res["updated_at"] = $result->last_modified_time;
            $res["created_at"] = $result->created;
            $jsonSerialized[] = $res;
        }

        if ($isOrbeonFetching) {
            logger()->info(OFFormSerializer::fromArrayToXmlDynamicDropdownData($jsonSerialized));
            return response(OFFormSerializer::fromArrayToXmlDynamicDropdownData($jsonSerialized))->header("Content-Type", "application/xml");
        }

        return response()->json(new OFDataCollection($jsonSerialized));
    }

    public function destroy(int $id): JsonResponse
    {
        DB::transaction(function () use ($id) {
            //As we should not manipulate with orbeon db structure, we need
            //to ensure that all orbeon_i_current_data records that point to
            //this record are deleted before we delete the record itself.
            $this->orbeonICurrentRepository->deleteWhere([
                "data_id" => $id
            ]);
            //We also need to make sure that all records that have document_id
            //same as record that we seek to delete are deleted. That""s because
            //of audit that orbeon does. Form more info see OFDataRepository.php
            //queryAndReturnNewestByDocumentId method.
            $recordToDel = $this->repository->find($id);
            $formMeta = $this->controlTextRepository->getFormMeta($recordToDel->id);

            $this->controlTextRepository->deleteWhere(["data_id" => $recordToDel->id]);
            $this->repository->deleteWhere([
                "document_id" => $recordToDel->document_id
            ]);

            //If we are deleting form definition
            if ($recordToDel->app === "orbeon" && $recordToDel->form === "builder") {
                if ($formMeta && !empty($formMeta["app_name"]) && !empty($formMeta["form_name"])) {
                    $this->formDefinitionRepository->deleteWhere([
                        "app" => $formMeta["app_name"],
                        "form" => $formMeta["form_name"],
                    ]);
                    $this->modelConfigRepository->deleteWhere([
                        "app_name" => $formMeta["app_name"],
                        "form_name" => $formMeta["form_name"],
                    ]);

                    $dataToDelete = $this->repository->query([
                        "app" => $formMeta["app_name"],
                        "form" => $formMeta["form_name"],
                    ]);
                    foreach ($dataToDelete as $data) {
                        $this->controlTextRepository->deleteWhere(["data_id" => $data->id]);
                        $this->orbeonICurrentRepository->deleteWhere(["data_id" => $data->id]);
                        $this->repository->delete($data->id);
                    }
                }
            }

        });

        return response()->json(null, 204);
    }
}
