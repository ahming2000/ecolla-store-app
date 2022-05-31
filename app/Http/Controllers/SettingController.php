<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Origin;
use App\Models\SystemConfig;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class SettingController extends Controller
{
    public function changeLanguage(string $lang): RedirectResponse
    {
        session()->put('lang', $lang);
        return response()->redirectTo(request('redirectTo') ?? '/');
    }

    public function settingPage(): Factory|View|Application
    {
        $origins = Origin::all();
        $categories = Category::all();

        return view('management.setting.index', compact('origins', 'categories'));
    }

    public function addOrigin(): JsonResponse
    {
        $name = request('name');
        $name_en = request('name_en');

        $origin = new Origin([
            'name' => $name,
            'name_en' => $name_en,
        ]);

        if ($origin->save()) {
            return response()->json(['origin' => $origin], 201);
        } else {
            return response()->json([], 400);
        }
    }

    public function addCategory(): JsonResponse
    {
        $name = request('name');
        $name_en = request('name_en');

        $category = new Category([
            'name' => $name,
            'name_en' => $name_en,
        ]);

        if ($category->save()) {
            return response()->json(['category' => $category], 201);
        } else {
            return response()->json([], 400);
        }
    }

    public function updateOrigin(Origin $origin): JsonResponse
    {
        $name = request('name');
        $name_en = request('name_en');

        if ($origin->update([
            'name' => $name,
            'name_en' => $name_en,
        ])) {
            return response()->json(['origin' => $origin]);
        } else {
            return response()->json([], 400);
        }
    }

    public function updateCategory(Category $category): JsonResponse
    {
        $name = request('name');
        $name_en = request('name_en');

        if ($category->update([
            'name' => $name,
            'name_en' => $name_en,
        ])) {
            return response()->json(['category' => $category]);
        } else {
            return response()->json([], 400);
        }
    }

    public function deleteOrigin(Origin $origin): JsonResponse
    {
        if ($origin->delete()) {
            return response()->json([], 204);
        } else {
            return response()->json([], 400);
        }
    }

    public function deleteCategory(Category $category): JsonResponse
    {
        if ($category->delete()) {
            return response()->json([], 204);
        } else {
            return response()->json([], 400);
        }
    }
}
