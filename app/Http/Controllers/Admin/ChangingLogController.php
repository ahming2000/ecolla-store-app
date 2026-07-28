<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Language;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;
use JsonException;

class ChangingLogController extends Controller
{
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
}
