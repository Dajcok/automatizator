<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    public function __construct($request = null)
    {
        parent::__construct($request);
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'generated_documents' => $this->resource->generated_documents,
        ];
    }
}
