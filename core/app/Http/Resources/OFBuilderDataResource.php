<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

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
            "id" => $this->id,
            "created_at" => $this->created,
            "updated_at" => $this->last_modified_time,
            "stage" => $this->stage,
            "document_id" => $this->document_id,
            "form_name" => $this->form_name,
            "form_title" => $this->form_title,
        ];
    }
}
