<?php

namespace App\Http\Resources\Core;

use App\Http\Resources\BaseCollection;

class SubmissionCollection extends BaseCollection
{
    public $collects = SubmissionResource::class;

    public function toArray($request): array
    {
        return [
            'data' => array_values($this->collection->toArray()),
        ];
    }
}
