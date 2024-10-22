<?php

namespace App\Http\Controllers;

use App\Http\Resources\OFDefinitionCollection;
use App\Http\Resources\OFDefinitionResource;
use App\Models\OrbeonFormDefinition;
use App\Repositories\OFDefinitionRepository;
use App\Repositories\OrbeonIControlTextRepository;
use App\Services\OrbeonServiceContract;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OFDefinitionController extends ResourceController
{
    public function __construct(
        OFDefinitionRepository                 $repository,
        OFDefinitionResource                   $resource,
        OFDefinitionCollection                 $collection,
        private readonly OrbeonServiceContract $service,
    )
    {
        parent::__construct($repository, $resource, $collection, OrbeonFormDefinition::class);
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

    public function render(string $app, string $form): Application|Response|ResponseFactory
    {
        $data = $this->service->render($app, $form);

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
                $definition->form_title = (string) $xml->title;
            } else {
                $definition->form_title = null;
            }
        });

        return response()->json(new OFDefinitionCollection($definitions));
    }
}
