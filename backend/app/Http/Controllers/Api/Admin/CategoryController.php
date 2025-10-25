<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\AdminLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Получить список категорий
     */
    public function index(Request $request): JsonResponse
    {
        $sortBy = $request->get('sort_by', 'id'); // id или created_at
        $sortOrder = $request->get('sort_order', 'asc'); // asc или desc
        
        $validSortFields = ['id', 'created_at', 'name'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'id';
        }
        
        $categories = Category::with(['parent', 'children', 'products'])
            ->orderBy($sortBy, $sortOrder)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Создать категорию
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category = Category::create($request->all());

        AdminLog::logAction(
            'created',
            'category',
            $category->name,
            $category->id,
            null,
            $request->ip()
        );

        return response()->json([
            'success' => true,
            'data' => $category,
        ], 201);
    }

    /**
     * Обновить категорию
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $oldData = $category->toArray();
        $category->update($request->all());

        AdminLog::logAction(
            'updated',
            'category',
            $category->name,
            $category->id,
            [
                'old' => $oldData,
                'new' => $category->toArray()
            ],
            $request->ip()
        );

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Удалить категорию
     */
    public function destroy(Category $category): JsonResponse
    {
        $categoryName = $category->name;
        $categoryId = $category->id;
        
        $category->delete();

        AdminLog::logAction(
            'deleted',
            'category',
            $categoryName,
            $categoryId,
            null,
            request()->ip()
        );

        return response()->json([
            'success' => true,
            'message' => 'Категория удалена',
        ]);
    }
}
