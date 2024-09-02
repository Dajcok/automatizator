<?php

namespace App\Repositories;

use App\Models\OrbeonFormData;

class OFDataRepository extends Repository
{
    public function __construct(OrbeonFormData $model)
    {
        parent::__construct($model);
    }
}
