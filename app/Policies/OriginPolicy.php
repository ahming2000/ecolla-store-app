<?php

namespace App\Policies;

use App\Enums\AccessLevel;
use App\Models\Origin;
use App\Models\User;

class OriginPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->access_level >= AccessLevel::VIEWER->value;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Origin $origin): bool
    {
        return $user->access_level >= AccessLevel::VIEWER->value;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->access_level >= AccessLevel::SUPERVISOR->value;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Origin $origin): bool
    {
        return $user->access_level >= AccessLevel::SUPERVISOR->value;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Origin $origin): bool
    {
        return $user->access_level >= AccessLevel::SUPERVISOR->value;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Origin $origin): bool
    {
        return $user->access_level >= AccessLevel::ADMIN->value;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Origin $origin): bool
    {
        return $user->access_level >= AccessLevel::ADMIN->value;
    }
}
