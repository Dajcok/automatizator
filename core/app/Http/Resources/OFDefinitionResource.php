<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OFDefinitionResource extends JsonResource
{
    public function __construct($resource = null)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'app' => $this->resource->app,
            'form_name' => $this->resource->form,
            'form_title' => $this->resource->form_title,
            'form_version' => $this->resource->form_version,
            'created_at' => $this->resource->created,
            'updated_at' => $this->resource->last_modified_time,
        ];
    }
}
