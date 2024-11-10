<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Resources\Json\JsonResource;

class ModelConfigResource extends JsonResource
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
            'column_config' => $this->resource->column_config,
            'form_type' => $this->resource->form_type,
        ];
    }
}
