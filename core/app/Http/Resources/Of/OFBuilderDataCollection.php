<?php

namespace App\Http\Resources\Of;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OFBuilderDataCollection extends ResourceCollection
{
    public $collects = OFBuilderDataResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => array_values($this->collection->toArray()),
        ];
    }
}
