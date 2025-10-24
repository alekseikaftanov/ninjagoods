<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Получить список категорий
     */
    public function index(): JsonResponse
    {
        $categories = Category::with(['children', 'products'])
            ->whereNull('parent_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
