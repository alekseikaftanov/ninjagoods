<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Получить список товаров
     */
    public function index(): JsonResponse
    {
        $products = Product::with('category')->get();

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

        $product->update($request->all());

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
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Товар удален',
        ]);
    }
}
