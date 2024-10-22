<?php

namespace App\Http\Resources\Of;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OFBuilderDataResource extends JsonResource
{
    public function __construct($request = null)
    {
        parent::__construct($request);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id,
            "created_at" => $this->resource->created,
            "updated_at" => $this->resource->last_modified_time,
            "stage" => $this->resource->stage,
            "document_id" => $this->resource->document_id,
            "form_name" => $this->resource->form_name,
            "form_title" => $this->resource->form_title,
            "is_draft" => $this->resource->is_draft,
        ];
    }
}
