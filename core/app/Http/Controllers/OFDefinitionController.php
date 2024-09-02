<?php

namespace App\Http\Controllers;

use App\Http\Resources\BaseCollection;
use App\Http\Resources\OFDefinitionResource;
use App\Models\OrbeonFormDefinition;
use App\Repositories\OFDefinitionRepository;
use App\Services\OrbeonServiceContract;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;

class OFDefinitionController extends ResourceController
{
    public function __construct(
        OFDefinitionRepository                 $repository,
        OFDefinitionResource                   $resource,
        BaseCollection                         $collection,
        private readonly OrbeonServiceContract $service
    )
    {
        parent::__construct($repository, $resource, $collection, OrbeonFormDefinition::class);
    }

    public function render(string $app, string $form): Application|Response|ResponseFactory
    {
        $data = $this->service->render($app, $form);

        $response = response($data['html'])
            ->header('Content-Type', 'text/html');

        if (!isset($data['cookies'])) {
            return $response;
        }

        foreach ($data['cookies'] as $cookie) {
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

        return $response;
    }
}
