<?php

namespace App\Integration\Acl\Base;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Responsibilities of this layer:
 *  - validate input data from transaction layer
 *  - serialize data to be stored
 *  - commit data to repository
 */
abstract class ACLShard
{
    public readonly string $id;

    protected array $errors = [];

    public function __construct(
        protected readonly array $data,
        protected readonly array $requiredFields
    ) {
        $this->id = Str::uuid()->toString();
        $this->validateInput();
    }

    /**
     * This commits the shard to it's given storage.
     *
     * @return mixed
     */
    abstract function commit(): Model;

    /**
     * This serializes the shard to be stored.
     *
     * @return array
     */
    protected function serialize(): array
    {
        throw new BadMethodCallException("Method not implemented.");
    }

    /**
     * Validates input data and sets errors if any.
     *
     * @return bool
     */
    function validateInput(): bool {
        foreach ($this->requiredFields as $field) {
            if (!isset($this->data[$field])) {
                $this->errors[] = "$field is required";
            }
        }

        return count($this->errors) === 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    abstract function toRepresentation();
}
