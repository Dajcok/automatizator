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

    /**
     * Retrieves the newest version of the data for a specific document_id.
     * Since OrbeonFormData model tracks multiple versions for auditing purposes,
     * we need to fetch the most recent entry based on the last_modified_time.
     *
     * @param array $query Contains the document_id to filter by.
     * @return mixed The latest OrbeonFormData entry for the given document_id.
     */
    public function queryAndReturnNewestByDocumentId(array $query): mixed
    {
        return $this->model
            ->where($query)
            ->where('deleted', '!=', '1')
            ->select('orbeon_form_data.*')
            ->joinSub(
                $this->model->select('document_id', \DB::raw('MAX(last_modified_time) as max_modified'))
                    ->groupBy('document_id'),
                'latest',
                function ($join) {
                    $join->on('orbeon_form_data.document_id', '=', 'latest.document_id')
                        ->on('orbeon_form_data.last_modified_time', '=', 'latest.max_modified');
                }
            )
            ->get();
    }
}
