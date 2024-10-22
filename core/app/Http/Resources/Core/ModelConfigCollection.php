<?php

namespace App\Http\Resources\Core;

use App\Http\Resources\BaseCollection;

class ModelConfigCollection extends BaseCollection
{
    public $collects = ModelConfigResource::class;

    public function toArray($request): array
    {
        return [
            'data' => array_values($this->collection->toArray()),
        ];
    }
}
