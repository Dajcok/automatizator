<?php

namespace App\Http\Controllers;

use App\Http\Resources\BaseCollection;
use App\Http\Resources\OFDataResource;
use App\Models\OrbeonFormDefinition;
use app\Repositories\OFDataRepository;
use App\Services\OrbeonServiceContract;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;

class OFDataController extends ResourceController
{
    public function __construct(
        OFDataRepository $repository,
        OFDataResource $resource,
        BaseCollection $collection,
        private readonly OrbeonServiceContract $service
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
}
