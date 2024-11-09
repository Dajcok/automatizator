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

    public function getFormDefinitionType(
        string $appName,
        string $formName,
    )
    {
        $res = $this->model->where([
            'app_name' => $appName,
            'form_name' => $formName,
        ])->select(
            'form_type',
        )->first();

        return $res ? $res->form_type : null;
    }
}
