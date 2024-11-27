<?php

namespace App\Integration\Acl\Base;

use InvalidArgumentException;

/**
 * Responsibilities of this layer:
 *  - get input data
 *  - format input data so each row can be used to spawn a transaction
 *  - spawn transactions for each row
 *  - start transactions
 */
abstract class ACLTransactionManager
{
    /** @var ACLTransaction[] $transactions */
    protected array $transactions = [];

    /**
     * Returns array of data to be used in transactions. Every element of the array is a separate transaction.
     * E.g.: data may come from excel, csv, etc. Whole array represents full data set where each element is a row.
     *
     * @return array<array>
     */
    abstract function getInputData(): array;

    /**
     * adds a transaction to the transaction manager
     *
     * @param array $row
     * @return void
     */
    abstract function spawnTransaction(array $row): void;

    /**
     * Starts the transaction manager. It should manage lifecycle of transactions.
     *
     * @return void
     */
    public function start(): void
    {
        /** @var array<array> $data */
        $data = $this->getInputData();

        foreach ($data as $row) {
            $this->spawnTransaction($row);
        }

        foreach ($this->transactions as $transaction) {
            $transaction->startTransaction();
        }
    }

    public function loadTransactions(): void
    {
        $data = $this->getInputData();

        foreach ($data as $row) {
            $this->spawnTransaction($row);
        }
    }
}
