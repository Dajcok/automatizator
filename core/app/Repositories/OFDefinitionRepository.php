<?php

namespace App\Repositories;

use App\Models\OrbeonFormDefinition;

class OFDefinitionRepository extends Repository
{
    public function __construct(OrbeonFormDefinition $model)
    {
        parent::__construct($model);
    }
}
