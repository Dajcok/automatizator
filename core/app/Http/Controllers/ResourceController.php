<?php

namespace App\Http\Controllers;

use app\Repositories\Repository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class ResourceController
 *
 * @package App\Http\Controllers\Abstract
 * @template TStoreRequest of FormRequest
 * @template TUpdateRequest of FormRequest
 */
abstract class ResourceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected readonly Repository $repository,
        protected readonly JsonResource $resource,
        protected readonly ResourceCollection $collection,
        protected string $modelName,
    ) {
        parent::__construct();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param TStoreRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    protected function performStore(FormRequest $request): JsonResponse
    {
        /** @var TStoreRequest $request */
        Gate::authorize('create', $this->modelName);
        $this->resource->resource = $this->repository->create($request->all());
        return response()->json($this->resource->toArray($request), SymfonyResponse::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int            $id
     * @param TUpdateRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    protected function performUpdate(int $id, FormRequest $request): JsonResponse
    {
        $model = $this->repository->find($id);
        Gate::authorize('update', $model);
        /** @var TUpdateRequest $request */
        $this->resource->resource = $this->repository->update($id, $request->validated());
        return response()->json($this->resource->toArray($request));
    }

    /**
     * Returns all resources if the user is authorized to view any resource.
     * Otherwise, it throws an AuthorizationException.
     *
     * @return Collection|LengthAwarePaginator
     * @throws AuthorizationException
     */
    protected function getAuthorizedResources(): Collection|LengthAwarePaginator
    {
        Gate::authorize('viewAny', $this->modelName);
        return $this->repository->all();
    }

    /**
     * Display a listing of the resource.
     * Authorization is handled in the getAuthorizedResources method.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function index(Request $request): JsonResponse
    {
        $this->collection->collection = $this->getAuthorizedResources();
        return response()->json($this->collection->toArray($request));
    }

    /**
     * Display the specified resource.
     *
     * @param int     $id
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $model = $this->repository->find($id);
        Gate::authorize('view', $model);
        $this->resource->resource = $model;
        return response()->json($this->resource->toArray($request));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $model = $this->repository->find($id);
        Gate::authorize('delete', $model);
        $this->repository->delete($id);
        return response()->json(null, SymfonyResponse::HTTP_NO_CONTENT);
    }
}
