<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccessLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function page(Request $request): Response
    {
        $users = $this->userService->getAllUsers(AccessLevel::getAccessLevelOptions());

        return Inertia::render('admin/user/Index', [
            'users' => UserResource::collection($users)->resolve($request),
        ]);
    }

    public function profilePage(): Response
    {
        return Inertia::render('admin/profile/Index');
    }

    public function create(CreateUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = $this->userService
            ->createUser(
                $data['username'],
                $data['password'],
                AccessLevel::tryFrom($data['access_level']),
            );

        return response()->json(
            UserResource::make($user)->resolve($request),
        );
    }

    public function deactivate(Request $request, User $user): JsonResponse
    {
        $user = $this->userService->deactivateUser($user);

        return response()->json(
            UserResource::make($user)->resolve($request),
        );
    }

    public function reactivate(Request $request, User $user): JsonResponse
    {
        $user = $this->userService->reactivateUser($user);

        return response()->json(
            UserResource::make($user)->resolve($request),
        );
    }

    public function destroy(User $user): HttpResponse
    {
        $this->userService->deleteUser($user);

        return response()->noContent();
    }
}
