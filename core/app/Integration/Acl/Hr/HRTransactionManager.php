<?php

namespace App\Integration\Acl\Hr;

use App\Repositories\Of\OFDataRepository;
use App\Integration\Acl\Base\ACLTransactionManager;
use App\Repositories\Of\OrbeonICurrentRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;

class HRTransactionManager extends ACLTransactionManager
{
    /** @var HRTransaction[] $transactions */
    protected array $transactions = [];

    public function __construct(
        private readonly OFDataRepository $repository,
        private readonly OrbeonICurrentRepository $orbeonICurrentRepository,
    )
    {}

    function getInputData(): array
    {
        $relativePath = __DIR__ . '/Resources/udaje.xlsx';

        if (!file_exists($relativePath)) {
            throw new \RuntimeException("File not found at path: $relativePath");
        }

        $spreadsheet = IOFactory::load($relativePath);

        $worksheet = $spreadsheet->getActiveSheet();

        $data = $worksheet->toArray();

        array_shift($data);

        return $data;
    }

    function spawnTransaction(array $row): void
    {
        $this->transactions[] = new HRTransaction($this->repository, $this->orbeonICurrentRepository, $row);
    }
}
