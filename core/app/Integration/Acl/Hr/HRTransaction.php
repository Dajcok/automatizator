<?php

namespace App\Integration\Acl\Hr;

use App\Repositories\Of\OFDataRepository;
use App\Integration\Acl\Base\ACLTransaction;
use App\Integration\Acl\Hr\Shards\SubcontractorShard;
use App\Repositories\Of\OrbeonICurrentRepository;

class HRTransaction extends ACLTransaction
{
    private SubcontractorShard $subcontractorShard;

    public function __construct(
        OFDataRepository $repository,
        OrbeonICurrentRepository $orbeonICurrentRepository,

        array            $row,
    )
    {
        parent::__construct($row);

        $this->subcontractorShard = new SubcontractorShard(
            $repository,
            $orbeonICurrentRepository,
            $this->parseRowForSubcontractorShard($row)
        );

        logger()->info('HRTransaction with id ' . $this->id . ' created');
    }

    function parseRowForSubcontractorShard(array $row): array
    {
        $pohlavie = str_contains($row[5], "ž") ? "W" : "M";

        return [
            "section-1" => [
                "control-1" => $row[2],
                "control-2" => $row[3],
                "control-3" => $row[17],
                "control-4" => $row[4],
                "control-5" => $pohlavie,
                "control-6" => $row[6],
                "control-7" => $row[7],
                "control-15" => $row[15],
                "control-16" => $row[16],
                "control-21" => $row[29],
                "control-24" => $row[18],
            ],
            "section-3" => [
                "control-10" => $row[10],
                "control-11" => $row[11],
                "control-12" => $row[12],
                "control-13" => $row[13],
                "control-14" => $row[14],
            ]
        ];
    }

    function startTransaction(): void
    {
        logger()->info('HRTransaction with id ' . $this->id . ' started');

        $this->subcontractorShard->commit();
    }
}
