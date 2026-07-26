<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getCategoriesWithItemCount();

        return response()->json($categories);
    }
}
