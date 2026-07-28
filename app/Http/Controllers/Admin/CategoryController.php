<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\SaveCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getCategoriesWithItemCount(true);

        return response()->json($categories);
    }

    public function store(SaveCategoryRequest $request): RedirectResponse
    {
        Category::query()->create($request->categoryData());

        return back();
    }

    public function update(
        SaveCategoryRequest $request,
        Category $category,
    ): RedirectResponse {
        $category->update($request->categoryData());

        return back();
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->id === Category::DEFAULT_CATEGORY_ID) {
            return back()->withErrors([
                'category' => 'The default category cannot be deleted.',
            ]);
        }

        if ($category->items()->exists()) {
            return back()->withErrors([
                'category' => 'A category assigned to items cannot be deleted.',
            ]);
        }

        $category->delete();

        return back();
    }
}
