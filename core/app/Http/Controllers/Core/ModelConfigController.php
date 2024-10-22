<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\Core\ModelConfigCollection;
use App\Http\Resources\Core\ModelConfigResource;
use App\Models\Core\ModelConfig;
use App\Repositories\Core\ModelConfigRepository;

class ModelConfigController extends ResourceController
{
    public function __construct(
        ModelConfigRepository $repository,
        ModelConfigResource $resource,
        ModelConfigCollection $collection
    )
    {
        parent::__construct(
            $repository,
            $resource,
            $collection,
            ModelConfig::class,
        );
    }
}
