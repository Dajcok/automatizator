<?php

namespace App\Repositories\Of;

use App\Models\Of\OrbeonFormDefinition;
use App\Repositories\Repository;

class OFDefinitionRepository extends Repository
{
    public function __construct(OrbeonFormDefinition $model)
    {
        parent::__construct($model);
    }
}
