<?php

namespace App\Http\Controllers\Of;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\BaseCollection;
use App\Http\Resources\Of\OFBuilderDataCollection;
use App\Http\Resources\Of\OFBuilderDataResource;
use App\Http\Resources\Of\OFDataCollection;
use App\Http\Resources\Of\OFDataResource;
use App\Models\Core\Submission;
use App\Models\Of\OrbeonFormData;
use App\Repositories\Core\ModelConfigRepository;
use App\Repositories\Core\SubmissionRepository;
use App\Repositories\Of\OFDataRepository;
use App\Repositories\Of\OFDefinitionRepository;
use App\Repositories\Of\OrbeonIControlTextRepository;
use App\Repositories\Of\OrbeonICurrentRepository;
use App\Serializers\OFFormSerializer;
use App\Services\Of\OFDataRepresentationService;
use App\Utils\LabelToKey;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Class OFDataController
 * Controller for handling Orbeon form data.
 * Main tables: orbeon_form_data
 *
 * @property OFDataRepository $repository
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
        private readonly ModelConfigRepository        $modelConfigRepository,
        private readonly SubmissionRepository         $submissionRepository,

        private readonly OFDataRepresentationService  $representationService
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

        $controlsToLabels = OFFormSerializer::fromXmlDefinitionToJsonControls($definition->xml, true);

        $dataWithControls = [];

        foreach ($controlsToLabels as $section => $controls) {
            $dataWithControls[$section] = [];

            foreach ($controls as $key => $label) {
                if (!isset($body[LabelToKey::convert($label)])) {
                    $dataWithControls[$section][$key] = null;
                    continue;
                }

                $dataWithControls[$section][$key] = $body[LabelToKey::convert($label)];
            }
        }

        $submissionReadyXml = OFFormSerializer::fromArrayToXmlSubmission($dataWithControls);

        $documentId = sha1(uniqid(mt_rand(), true));

        DB::transaction(function () use ($app, $form, $body, $submissionReadyXml, $documentId, $definition) {
            /** @var OrbeonFormData $inserted */
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
                "message" => "Submission created",
                "document_id" => $documentId
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

        $queryParams = $request->query();

        $xmlFilters = [];
        foreach ($queryParams as $key => $value) {
            //every query param that is not "verbose" is considered as an xml filter
            if (!str_contains($key, "verbose")) {
                $xmlFilters[$key] = $value;
            }
        }

        /**
         * In orbeon both form builder data and submitted form data are stored in orbeon_form_data table.
         * This Indicates whether we are retrieving form builder data or data of submitted forms
         */
        $retrievingFormBuilderData = false;
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

            $retrievingFormBuilderData = $orbeonApp === "orbeon";

            $app = "orbeon";
            $form = "builder";
        }

        if ($retrievingFormBuilderData) {
            if (!$parentApp) {
                throw new Exception("Parent app is missing");
            }

            $representation = $this->representationService->toFormBuilderDataRepresentation($parentApp);

            if ($request->get("withFormType", false)) {
                foreach ($representation as $item) {
                    $item->form_type = $this->modelConfigRepository->getFormDefinitionType(
                        $parentApp,
                        $item->form_name
                    );
                }
            }

            $resources = OFBuilderDataResource::collection($representation);
            return response()->json(new OFBuilderDataCollection($resources));
        }

        //We use internal host to determine whether Orbeon is fetching data from our core api
        $isOrbeonFetching = $request->headers->get("Host") === config("app.service_url");

        if ($isOrbeonFetching) {
            $verbose = true;
        }

        $serializedDefinition = $this->representationService->getSerializedDefinition($app, $form);

        $results = $this->repository->queryAndReturnNewestGroupedByDocumentId($app, $form, $xmlFilters);

        if (!count($results)) {
            return response()->json([
                "data" => [],
            ]);
        }

        $jsonSerialized = [];

        foreach ($results as $result) {
            $res = $this->representationService->toFormDataRepresentation(
                $result,
                $serializedDefinition,
                $verbose,
                $isOrbeonFetching
            );

            try {
                /** @var Submission $submissionMetaData */
                $submissionMetaData = $this->submissionRepository->find($result->document_id);
                $res['generated_documents'] = $submissionMetaData->generated_documents;
            } catch (Exception $e) {
                logger()->info("Submission meta data not found for " . $result->document_id);
                $res["generated_documents"] = [];
            }

            $jsonSerialized[] = $res;
        }

        if ($isOrbeonFetching) {
            logger()->info(OFFormSerializer::fromArrayToXmlDynamicDropdownData($jsonSerialized));
            return response(OFFormSerializer::fromArrayToXmlDynamicDropdownData($jsonSerialized))->header("Content-Type", "application/xml");
        }

        return response()->json(new OFDataCollection($jsonSerialized));
    }

    /**
     * @throws Exception
     */
    public function showByDocumentId(string $documentId, Request $request): JsonResponse
    {
        /** @var OrbeonFormData $data */
        $data = $this->repository->retrieveNewestByDocumentId($documentId);

        return $this->returnShowResponse($data, $request);
    }

    /**
     * @throws Exception
     */
    public function show(int $id, Request $request): JsonResponse
    {
        /** @var OrbeonFormData $data */
        $data = $this->repository->find($id);

        return $this->returnShowResponse($data, $request);
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
            //queryAndReturnNewestGroupedByDocumentId method.
            /** @var OrbeonFormData $recordToDel */
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

    /**
     * @throws Exception
     */
    private function returnShowResponse(
        OrbeonFormData $data,
        Request        $request
    ): JsonResponse
    {
        $verbose = $request->get("verbose", false);
        $serializedDefinition = $this->representationService->getSerializedDefinition($data->app, $data->form);

        $res = $this->representationService->toFormDataRepresentation(
            $data,
            $serializedDefinition,
            $verbose
        );

        if ($request->get("withFormType", false)) {
            $res["form_type"] = $this->modelConfigRepository->getFormDefinitionType(
                $data->app,
                $data->form
            );
        }

        $res["form_name"] = $data->form;

        return response()->json(new OFDataResource($res));
    }
}
