<?php

namespace App\Integration\Acl\Base;

use Illuminate\Support\Str;

/**
 * Responsibilities of this layer:
 *  - map input data to shards
 *  - create shards with this data
 *  - start commiting the shards
 */
abstract class ACLTransaction
{
    public string $id;

    public function __construct(
        protected array $row,
    ) {
        $this->id = Str::uuid()->toString();
    }

    abstract function startTransaction();
}
