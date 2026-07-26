<?php

namespace App\Services;

use App\Enums\AccessLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * @param  AccessLevel|list<AccessLevel>  $accessLevels
     * @return Collection<int, User>
     */
    public function getAllUsers(AccessLevel|array $accessLevels): Collection
    {
        $accessLevels = is_array($accessLevels) ? $accessLevels : [$accessLevels];

        return User::query()
            ->whereIn('access_level', $accessLevels)
            ->get();
    }

    public function createUser(string $username, string $password, ?AccessLevel $accessLevel): User
    {
        return User::create([
            'username' => $username,
            'password' => Hash::make($password),
            'access_level' => $accessLevel ?? AccessLevel::VIEWER,
            'is_enabled' => true,
        ]);
    }

    public function deactivateUser(User $user): User
    {
        $user->update(['is_enabled' => false]);

        return $user;
    }

    public function reactivateUser(User $user): User
    {
        $user->update(['is_enabled' => true]);

        return $user;
    }

    public function deleteUser(User $user): void
    {
        $user->update(['is_enabled' => false]);
        $user->delete();
    }
}
