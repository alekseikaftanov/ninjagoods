<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Оформить заказ
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:telegram_users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ]);

        $total = 0;
        $items = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $price = $product->price;
            $quantity = $item['quantity'];
            $itemTotal = $price * $quantity;

            $items[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
            ];

            $total += $itemTotal;
        }

        $order = Order::create([
            'user_id' => $request->user_id,
            'items' => $items,
            'total' => $total,
        ]);

        return response()->json([
            'success' => true,
            'data' => $order->load('user'),
        ], 201);
    }
}
