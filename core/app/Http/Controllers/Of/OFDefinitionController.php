<?php

namespace App\Http\Controllers\Of;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\Of\OFDefinitionCollection;
use App\Http\Resources\Of\OFDefinitionResource;
use App\Models\Of\OrbeonFormDefinition;
use App\Repositories\Core\ModelConfigRepository;
use App\Repositories\Of\OFDefinitionRepository;
use App\Serializers\OFFormSerializer;
use App\Services\OrbeonServiceContract;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

readonly class OFDefinitionController
{
    public function __construct(
        private OFDefinitionRepository $repository,
        private OrbeonServiceContract  $service,
        private ModelConfigRepository  $modelConfigRepository
    )
    {
    }

    private function forwardCookies(Response $response, array $cookies): void
    {
        foreach ($cookies as $cookie) {
            $response->cookie(
                $cookie->name,
                $cookie->value,
                $cookie->minutes ?? null,
                $cookie->path ?? null,
                $cookie->domain ?? null,
                $cookie->secure ?? true,
                $cookie->httpOnly ?? true,
                $cookie->raw ?? false,
                $cookie->sameSite ?? 'none'
            );
        }
    }

    public function newForm(string $app): Application|Response|ResponseFactory
    {
        $data = $this->service->builder($app);

        $response = response($data['html'])
            ->header('Content-Type', 'text/html');

        if (isset($data['cookies'])) {
            $this->forwardCookies($response, $data['cookies']);
        }

        return $response;
    }

    public function editForm(string $app, string $docId): Application|Response|ResponseFactory
    {
        $data = $this->service->builder($app, $docId);

        $response = response($data['html'])
            ->header('Content-Type', 'text/html');

        if (isset($data['cookies'])) {
            $this->forwardCookies($response, $data['cookies']);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function render(Request $request): Application|Response|ResponseFactory
    {
        $app = $request->route("app");
        $form = $request->route("form");

        $context = $request->query("context", null);

        if($context) {
            $context = json_decode(base64_decode($context), true);
        }

        $data = $this->service->render($app, $form, null, $context);

        $response = response($data['html'])
            ->header('Content-Type', 'text/html');

        if (isset($data['cookies'])) {
            $this->forwardCookies($response, $data['cookies']);
        }

        return $response;
    }

    public function renderEdit(string $app, string $form, string $docId): Application|Response|ResponseFactory
    {
        $data = $this->service->render($app, $form, $docId);

        $response = response($data['html'])
            ->header('Content-Type', 'text/html');

        if (isset($data['cookies'])) {
            $this->forwardCookies($response, $data['cookies']);
        }

        return $response;
    }

    public function index(Request $request): JsonResponse
    {
        $app = $request->route("app");

        $definitions = $this->repository->query([
            "app" => $app,
        ]);

        $definitions->each(function ($definition) {
            $xml = simplexml_load_string($definition->form_metadata);

            if (isset($xml->title)) {
                $definition->form_title = (string)$xml->title;
            } else {
                $definition->form_title = null;
            }
        });

        return response()->json(new OFDefinitionCollection($definitions));
    }

    /**
     * @throws Exception
     */
    public function show(Request $request): JsonResponse
    {
        $app = $request->route("app");
        $form = $request->route("form");

        $definition = $this->repository->query([
            "app" => $app,
            "form" => $form,
        ]);

        if (!count($definition)) {
            throw new Exception("Form definition not found");
        }

        $serializedDefinition = OFFormSerializer::fromXmlDefinitionToJsonControls($definition[0]->xml);

        return response()->json([
            "definition" => $serializedDefinition,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $app = $request->route("app");
        $form = $request->route("form");

        $this->repository->deleteWhere([
            "app" => $app,
            "form" => $form,
        ]);

        return response()->json([
            "message" => "Form definition deleted",
        ]);
    }

    /**
     * This method retrieves all form names that are referencing through direct relation
     * to the given form name.
     *
     * E.g. If form A has select control with references to the submissions of form B,
     * then getRelatedTemplateFormNames for form B will return form A.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getRelatedTemplateFormNames(Request $request): JsonResponse
    {
        $app = $request->route("app");
        $form = $request->route("form");

        $allTemplateFormNamesInApp = $this->modelConfigRepository->query([
            "app_name" => $app,
            "form_type" => "template",
        ])->pluck("form_name");

        $templateDefinitions = $this->repository->queryAndReturnNewest([
            "app" => $app,
            "form" => $allTemplateFormNamesInApp,
        ], true);

        $res = [];

        foreach ($templateDefinitions as $templateDefinition) {
            $relatedForms = OFFormSerializer::fromXmlDefinitionToRelatedForms($templateDefinition->xml, $app);

            if (in_array($form, array_column($relatedForms, "form"))) {
                $result = array_values(array_filter($relatedForms, function($item) use ($form) {
                    return $item["form"] === $form;
                }))[0] ?? null;

                if ($result) {
                    $res[] = [
                        "form" => $templateDefinition->form,
                        "control" => $result["controlName"],
                    ];
                }
            }
        }

        return response()->json(["data" => $res]);
    }

}
