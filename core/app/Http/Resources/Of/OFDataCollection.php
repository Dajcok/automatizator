<?php

namespace App\Http\Resources\Of;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OFDataCollection extends ResourceCollection
{
    public $collects = OFDataResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
