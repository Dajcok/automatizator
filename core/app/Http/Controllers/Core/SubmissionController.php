<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Core\SubmissionResource;
use App\Repositories\Core\SubmissionRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

readonly class SubmissionController
{
    use AuthorizesRequests;

    public function __construct(
        private SubmissionRepository $repository,
    )
    {}

    public function update(
        string $documentId,
        Request $request
    ): JsonResponse
    {
        $data = $this->repository->updateOrCreateAndUpdate($documentId, $request->all());
        $response = new SubmissionResource();
        $response->resource = $data;

        return response()->json([
            'message' => 'Submission updated successfully',
            'data' => $response->toArray($request),
        ]);
    }
}
