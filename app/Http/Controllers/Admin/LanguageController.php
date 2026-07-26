<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Language;
use App\Http\Controllers\Controller;
use App\Http\Requests\Language\UpdateLanguageRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    public function update(UpdateLanguageRequest $request): RedirectResponse
    {
        $language = Language::from($request->validated('lang'));
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $user->lang = $language->value;
        $user->save();

        return back();
    }
}
