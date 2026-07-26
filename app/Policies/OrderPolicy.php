<?php

namespace App\Policies;

use App\Enums\AccessLevel;
use App\Enums\Status;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
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
    public function view(User $user, Order $order): bool
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
    public function update(User $user, Order $order): bool
    {
        return $user->access_level >= AccessLevel::SUPERVISOR->value;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return ($user->access_level == AccessLevel::EDITOR->value && $order->status == Status::PENDING)
            || $user->access_level >= AccessLevel::SUPERVISOR->value;
    }

    public function updateTrackingNumber(User $user, Order $order): bool
    {
        return ($user->access_level == AccessLevel::EDITOR->value && $order->status == Status::PENDING)
            || $user->access_level >= AccessLevel::SUPERVISOR->value;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return ($user->access_level == AccessLevel::SUPERVISOR->value && $order->status == Status::PENDING)
            || $user->access_level >= AccessLevel::ADMIN->value;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->access_level >= AccessLevel::ADMIN->value;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->access_level >= AccessLevel::ADMIN->value;
    }
}
