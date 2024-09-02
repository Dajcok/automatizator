<?php

namespace App\Policies;

//use App\Models\OrbeonFormDefinition;
use App\Models\User;

class OFDefinitionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user = null): bool
    {
        return true;
    }

//    /**
//     * Determine whether the user can view the model.
//     */
//    public function view(User $user = null, OrbeonFormDefinition $orbeonFormDefinition): bool
//    {
//        return true;
//    }
//
//    /**
//     * Determine whether the user can create models.
//     */
//    public function create(User $user = null): bool
//    {
//        return true;
//    }
//
//    /**
//     * Determine whether the user can update the model.
//     */
//    public function update(User $user = null, OrbeonFormDefinition $orbeonFormDefinition): bool
//    {
//        return true;
//    }
//
//    /**
//     * Determine whether the user can delete the model.
//     */
//    public function delete(User $user = null, OrbeonFormDefinition $orbeonFormDefinition): bool
//    {
//        return true;
//    }
//
//    /**
//     * Determine whether the user can restore the model.
//     */
//    public function restore(User $user = null, OrbeonFormDefinition $orbeonFormDefinition): bool
//    {
//        return true;
//    }
//
//    /**
//     * Determine whether the user can permanently delete the model.
//     */
//    public function forceDelete(User $user = null, OrbeonFormDefinition $orbeonFormDefinition): bool
//    {
//        return true;
//    }
}
