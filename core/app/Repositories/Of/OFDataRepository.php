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
     * It's grouped by document_id
     *
     * @param string $app The application name.
     * @param string $form The form name.
     * @param array $xmlFilters Contains strings that are applied to the XML filters as substring matches.
     * @return mixed The latest OrbeonFormData entry for the given document_id.
     */
    public function queryAndReturnNewestGroupedByDocumentId(string $app, string $form, array $xmlFilters = []): mixed
    {
        $builder = $this->model
            ->where([
                'app' => $app,
                'form' => $form
            ])
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
            );

        foreach ($xmlFilters as $nodePath => $val) {
            $builder->whereRaw(
                "(xpath('/form/$nodePath/text()', orbeon_form_data.xml::xml, ARRAY[ARRAY['fr', 'http://orbeon.org/oxf/xml/form-runner']]))[1]::TEXT LIKE ?",
                ["%$val%"]
            );
        }

        return $builder->get();
    }

    public function retrieveNewestByDocumentId(string $documentId): mixed
    {
        return $this->model
            ->where('orbeon_form_data.document_id', $documentId)
            ->where('orbeon_form_data.deleted', '!=', '1')
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
            ->first();
    }
}
