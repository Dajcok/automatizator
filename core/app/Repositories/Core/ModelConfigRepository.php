<?php

namespace App\Repositories\Core;

use App\Models\Core\ModelConfig;
use App\Repositories\Repository;

class ModelConfigRepository extends Repository
{
    public function __construct(ModelConfig $model)
    {
        parent::__construct($model);
    }
}
