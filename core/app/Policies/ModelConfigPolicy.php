<?php

namespace App\Policies;

use App\Models\Core\ModelConfig;

class ModelConfigPolicy
{
    public function create($user, ModelConfig $modelConfig): bool
    {
        return true;
    }

    public function view($user, ModelConfig $modelConfig): bool
    {
        return true;
    }

    public function update($user, ModelConfig $modelConfig): bool
    {
        return true;
    }

    public function delete($user, ModelConfig $modelConfig): bool
    {
        return true;
    }
}
