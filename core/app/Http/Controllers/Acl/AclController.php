<?php

namespace App\Http\Controllers\Acl;

use App\Repositories\Of\OrbeonICurrentRepository;
use Illuminate\Http\JsonResponse;
use App\Repositories\Of\OFDataRepository;
use App\Integration\Acl\Hr\HRTransactionManager;

readonly class AclController
{
    public function __construct(
        private OFDataRepository         $repository,
        private OrbeonICurrentRepository $orbeonICurrentRepository,
    )
    {}

    public function migrate(string $integrationName): JsonResponse
    {
        if($integrationName === 'hr') {
            $manager = new HRTransactionManager(
                $this->repository,
                $this->orbeonICurrentRepository
            );
            $manager->start();
        } else {
            return response()->json([
                'message' => 'Integration not found'
            ], 404);
        }


        return response()->json([
            'message' => 'Migrated successfully'
        ]);
    }
}
