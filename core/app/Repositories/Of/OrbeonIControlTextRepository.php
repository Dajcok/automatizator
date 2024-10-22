<?php

namespace App\Repositories\Of;

use App\Models\Of\OrbeonIControlText;
use App\Repositories\Repository;

class OrbeonIControlTextRepository extends Repository
{
    public function __construct(OrbeonIControlText $model)
    {
        parent::__construct($model);
    }

    public function getFormMeta($formId): array|null
    {
        $results = $this->query([
            "data_id" => $formId
        ]);

        if ($results->count() == 0) {
            return null;
        }

        $titleRes = $results->filter(function ($item) {
            return str_contains($item->control, 'title');
        });

        $nameRes = $results->filter(function ($item) {
            return str_contains($item->control, 'form-name');
        });

        $appNameRes = $results->filter(function ($item) {
            return str_contains($item->control, 'application-name');
        });

        return [
            "form_name" => $nameRes->first()->val,
            "form_title" => $titleRes->first()->val,
            "app_name" => $appNameRes->first()->val
        ];
    }
}
