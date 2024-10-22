<?php

namespace App\Http\Resources\Of;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OFDataResource extends JsonResource
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
        return parent::toArray($request);
    }
}
