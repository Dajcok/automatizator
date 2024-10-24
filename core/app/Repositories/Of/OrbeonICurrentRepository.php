<?php

namespace App\Repositories\Of;

use App\Models\Of\OrbeonICurrent;
use App\Repositories\Repository;

class OrbeonICurrentRepository extends Repository
{
    public function __construct(
        OrbeonICurrent $model
    )
    {
        parent::__construct(
            $model
        );
    }
}
