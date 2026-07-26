<?php

namespace App\Providers;

use App\Facades\AppSetting;
use App\Services\SettingService;
use Illuminate\Support\ServiceProvider;
use Inertia\ExceptionResponse;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<int, int>
     */
    private const ERROR_STATUS_CODES = [
        Response::HTTP_INTERNAL_SERVER_ERROR,
        Response::HTTP_SERVICE_UNAVAILABLE,
        Response::HTTP_BAD_REQUEST,
        Response::HTTP_UNAUTHORIZED,
        Response::HTTP_FORBIDDEN,
        Response::HTTP_NOT_FOUND,
    ];

    private const PAGE_EXPIRED_STATUS_CODE = 419;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(AppSetting::class, function () {
            return new SettingService;
        });

        $this->configureInertiaExceptionHandling();
    }

    private function configureInertiaExceptionHandling(): void
    {
        Inertia::handleExceptionsUsing(function (ExceptionResponse $response): ExceptionResponse|Response|null {
            $statusCode = $response->statusCode();

            if ((bool) config('app.debug') && $statusCode !== Response::HTTP_NOT_FOUND) {
                return null;
            }

            if ($statusCode === self::PAGE_EXPIRED_STATUS_CODE) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }

            if (! in_array($statusCode, self::ERROR_STATUS_CODES, true)) {
                return null;
            }

            $component = $response->request->is(
                'admin',
                'admin/*',
                'ajax/admin',
                'ajax/admin/*',
            ) ? 'error/Admin' : 'error/Shop';

            return $response
                ->render($component, ['status' => $statusCode])
                ->withSharedData();
        });
    }
}
