<?php

namespace App\Repositories\Of;

use App\Models\Of\OrbeonFormData;
use App\Repositories\Repository;

class OFDataRepository extends Repository
{
    public function __construct(OrbeonFormData $model)
    {
        parent::__construct($model);
    }
}
