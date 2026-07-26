<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Language;
use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Services\OriginService;
use App\Services\SettingService;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;
use JsonException;

class AdminController extends Controller
{
    public function __construct(
        private readonly OriginService $originService,
        private readonly CategoryService $categoryService,
        private readonly SettingService $settingService,
    ) {}

    /**
     * @throws JsonException
     */
    public function changingLogPage(): Response
    {
        $notes = [
            Language::EN->value => File::json(
                base_path('docs/changing-logs.en.json'),
                JSON_THROW_ON_ERROR,
            ),
            Language::ZH->value => File::json(
                base_path('docs/changing-logs.zh.json'),
                JSON_THROW_ON_ERROR,
            ),
        ];

        return Inertia::render(
            'admin/changing-log/Index',
            compact('notes')
        );
    }

    public function settingPage(): Response
    {
        $origins = $this->originService->getOriginsWithItemCount(true);
        $categories = $this->categoryService->getCategoriesWithItemCount(true);
        $shipping = $this->settingService->getShippingSettings();

        return Inertia::render(
            'admin/setting/Index',
            compact('origins', 'categories', 'shipping'),
        );
    }
}
