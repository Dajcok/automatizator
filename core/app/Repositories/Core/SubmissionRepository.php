<?php

namespace App\Repositories\Core;

use App\Models\Core\Submission;
use App\Repositories\Repository;

class SubmissionRepository extends Repository
{
    public function __construct(
        Submission $model
    )
    {
        parent::__construct(
            $model
        );
    }

    public function updateOrCreateAndUpdate(
        string $documentId,
        array $data
    ): Submission
    {
        $submission = $this->model->firstOrCreate(
            ['id' => $documentId],
            ['generated_documents' => $data['generated_documents']]
        );

        $submission->update($data);

        return $submission;
    }
}
