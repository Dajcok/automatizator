<?php

namespace App\Http\Resources\Of;

use App\Http\Resources\BaseCollection;
use Illuminate\Http\Request;

class OFDefinitionCollection extends BaseCollection
{
    public $collects = OFDefinitionResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'definitions' => $this->collection,
        ];
    }
}
