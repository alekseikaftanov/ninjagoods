<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $validSortFields = ['id', 'created_at', 'total'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'created_at';
        }
        
        $orders = Order::with('user')
            ->orderBy($sortBy, $sortOrder)
            ->get();

        // Добавляем информацию о товарах к каждому заказу
        $orders->each(function ($order) {
            $order->items = collect($order->items)->map(function ($item) {
                // Попробуем найти товар по ID
                $product = \App\Models\Product::find($item['product_id']);
                if ($product) {
                    $item['product_name'] = $product->name;
                }
                return $item;
            })->toArray();
        });

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load('user');
        
        // Добавляем информацию о товарах
        $order->items = collect($order->items)->map(function ($item) {
            $product = \App\Models\Product::find($item['product_id']);
            if ($product) {
                $item['product_name'] = $product->name;
                $item['product_description'] = $product->description;
                $item['product_image'] = $product->photo_url;
            }
            return $item;
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Заказ удален',
        ]);
    }
}
