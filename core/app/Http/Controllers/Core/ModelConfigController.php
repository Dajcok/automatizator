<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\ResourceController;
use App\Http\Requests\ModelConfigRequest;
use App\Http\Requests\ModelConfigUpdateRequest;
use App\Http\Resources\Core\ModelConfigCollection;
use App\Http\Resources\Core\ModelConfigResource;
use App\Models\Core\ModelConfig;
use App\Repositories\Core\ModelConfigRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

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

    /**
     * @throws AuthorizationException
     */
    public function store(ModelConfigRequest $request): \Illuminate\Http\JsonResponse
    {
        return $this->performStore($request);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(ModelConfigUpdateRequest $request, Int $id): \Illuminate\Http\JsonResponse
    {
        return $this->performUpdate($id, $request);
    }

    public function showWithFormNameAndAppName(Request $request): \Illuminate\Http\JsonResponse
    {
        $formName = $request->route('form');
        $appName = $request->route('app');

        if(!$formName || !$appName){
            return response()->json([
                'message' => 'Invalid request'
            ], 400);
        }

        $res= $this->repository->query([
            'form_name' => $formName,
            'app_name' => $appName
        ]);

        if(!count($res)){
            return response()->json([
                'message' => 'No data found'
            ], 404);
        }

        $this->resource->resource = $res[0];
        return response()->json($this->resource->toArray($request));
    }
}
