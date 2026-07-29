<?php

namespace App\Http\Middleware;

use App\Http\Resources\UserResource;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        private readonly SettingService $settingService,
    ) {}

    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $sharedData = [
            ...parent::share($request),
            'csrf' => csrf_token(),
            'auth' => [
                'user' => $request->user()
                    ? UserResource::make($request->user())->resolve($request)
                    : null,
            ],
        ];

        if (
            ! $request->routeIs('shop.*')
            || $request->routeIs('shop.landing.page')
        ) {
            return $sharedData;
        }

        return [
            ...$sharedData,
            'shop' => fn (): array => [
                'freeShipping' => $this->settingService
                    ->getShippingSettings()['freeShipping'],
            ],
        ];
    }
}
