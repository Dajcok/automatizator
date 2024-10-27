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

    public function queryAndReturnNewest(array $query): mixed
    {
        return $this->model
            ->where(function ($queryBuilder) use ($query) {
                foreach ($query as $key => $value) {
                    $queryBuilder->where("orbeon_form_definition.$key", $value);
                }
            })
            ->where('orbeon_form_definition.deleted', '!=', '1')
            ->joinSub(
                $this->model->select('orbeon_form_definition.app', 'orbeon_form_definition.form', \DB::raw('MAX(orbeon_form_definition.last_modified_time) as max_modified'))
                    ->groupBy('orbeon_form_definition.app', 'orbeon_form_definition.form'),
                'latest',
                function ($join) {
                    $join->on('orbeon_form_definition.app', '=', 'latest.app')
                        ->on('orbeon_form_definition.form', '=', 'latest.form')
                        ->on('orbeon_form_definition.last_modified_time', '=', 'latest.max_modified');
                }
            )
            ->select('orbeon_form_definition.*')
            ->get();
    }


}
