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
        $origins = Origin::query()
            ->where('deleted_at', '=', null)
            ->get();

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
            return response()->json(['isCreated' => true, 'model' => $origin]);
        } else {
            return response()->json(['isCreated' => false]);
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
            return response()->json(['isCreated' => true, 'model' => $category]);
        } else {
            return response()->json(['isCreated' => false]);
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
            return response()->json(['isUpdated' => true, 'model' => $origin]);
        } else {
            return response()->json(['isUpdated' => false]);
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
            return response()->json(['isUpdated' => true, 'model' => $category]);
        } else {
            return response()->json(['isUpdated' => false]);
        }
    }

    public function deleteOrigin(Origin $origin): JsonResponse
    {
        if ($origin->delete()) {
            return response()->json(['isDeleted' => true]);
        } else {
            return response()->json(['isDeleted' => false]);
        }
    }

    public function deleteCategory(Category $category): JsonResponse
    {
        if ($category->delete()) {
            return response()->json(['isDeleted' => true]);
        } else {
            return response()->json(['isDeleted' => false]);
        }
    }

    public function updateShippingFee(): JsonResponse
    {
        $fee = request('shipping_fee');

        if (SystemConfig::query()
            ->where('name', '=', 'shipping_fee')
            ->update(['value' => $fee])
        ) {
            return response()->json(['isUpdated' => true]);
        } else {
            return response()->json(['isUpdated' => false]);
        }
    }

    public function updateShippingDiscount()
    {
        if (request()->has('shipping_discount_is_activated')) {
            $isActivated = request('shipping_discount_is_activated');

            if (SystemConfig::query()
                ->where('name', '=', 'shipping_discount_is_activated')
                ->update(['value' => $isActivated])
            ) {
                return response()->json(['isUpdated' => true]);
            } else {
                return response()->json(['isUpdated' => false]);
            }
        } else {
            $threshold = request('shipping_discount_threshold');
            $desc = request('shipping_discount_desc');

            $thresholdIsUpdated = SystemConfig::query()
                ->where('name', '=', 'shipping_discount_threshold')
                ->update(['value' => $threshold]);

            $descIsUpdated = SystemConfig::query()
                ->where('name', '=', 'shipping_discount_desc')
                ->update(['value' => $desc]);

            if ($thresholdIsUpdated && $descIsUpdated) {
                return response()->json(['isUpdated' => true]);
            } else {
                return response()->json(['isUpdated' => false]);
            }
        }
    }
}
