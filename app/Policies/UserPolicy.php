<?php

namespace App\Policies;

use App\Enums\AccessLevel;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->access_level >= AccessLevel::ADMIN->value;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->access_level >= AccessLevel::ADMIN->value;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->access_level >= AccessLevel::ADMIN->value;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->access_level >= AccessLevel::ADMIN->value;
    }

    public function updatePassword(User $user, User $model): bool
    {
        return $user->access_level >= AccessLevel::VIEWER->value
            && $user->id == $model->id;
    }

    public function deactivate(User $user, User $model): bool
    {
        return $user->access_level >= AccessLevel::ADMIN->value
            && $user->isNot($model);
    }

    public function reactivate(User $user, User $model): bool
    {
        return $this->deactivate($user, $model);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->access_level >= AccessLevel::ADMIN->value
            && $user->isNot($model);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->access_level >= AccessLevel::ADMIN->value;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->access_level >= AccessLevel::ADMIN->value;
    }
}
