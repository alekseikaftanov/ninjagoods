<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\AdminLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Получить список товаров
     */
    public function index(Request $request): JsonResponse
    {
        $sortBy = $request->get('sort_by', 'id'); // id или created_at
        $sortOrder = $request->get('sort_order', 'asc'); // asc или desc
        
        $validSortFields = ['id', 'created_at', 'name', 'price'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'id';
        }
        
        $products = Product::with('category')
            ->orderBy($sortBy, $sortOrder)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Создать товар
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'photo_url' => 'required|string|max:500',
            'description' => 'required|string',
            'unit' => 'required|in:штуки,килограммы',
            'price' => 'required|numeric|min:0',
            'min_order' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create($request->all());

        AdminLog::logAction(
            'created',
            'product',
            $product->name,
            $product->id,
            null,
            $request->ip()
        );

        return response()->json([
            'success' => true,
            'data' => $product->load('category'),
        ], 201);
    }

    /**
     * Обновить товар
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'photo_url' => 'required|string|max:500',
            'description' => 'required|string',
            'unit' => 'required|in:штуки,килограммы',
            'price' => 'required|numeric|min:0',
            'min_order' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
        ]);

        $oldData = $product->toArray();
        $product->update($request->all());

        AdminLog::logAction(
            'updated',
            'product',
            $product->name,
            $product->id,
            [
                'old' => $oldData,
                'new' => $product->toArray()
            ],
            $request->ip()
        );

        return response()->json([
            'success' => true,
            'data' => $product->load('category'),
        ]);
    }

    /**
     * Удалить товар
     */
    public function destroy(Product $product): JsonResponse
    {
        $productName = $product->name;
        $productId = $product->id;
        
        $product->delete();

        AdminLog::logAction(
            'deleted',
            'product',
            $productName,
            $productId,
            null,
            request()->ip()
        );

        return response()->json([
            'success' => true,
            'message' => 'Товар удален',
        ]);
    }
}
