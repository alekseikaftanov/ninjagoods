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
        
        // Load orders with related data (user for legacy orders, buyer for B2B orders)
        $orders = Order::with(['user', 'buyer', 'organization', 'orderItems.employee'])
            ->orderBy($sortBy, $sortOrder)
            ->get();

        // Format orders for admin display
        $orders->each(function ($order) {
            // Ensure buyer and organization data are loaded
            // They will be used by the frontend directly
            
            // Load items from order_items table for B2B orders
            if ($order->orderItems && $order->orderItems->count() > 0) {
                $order->items = $order->orderItems->map(function ($item) {
                    $product = \App\Models\Product::where('name', $item->product_name)->first();
                    return [
                        'product_id' => $product ? $product->id : 0,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'price' => $product ? $product->price : 0,
                    ];
                })->toArray();
            } else {
                // For legacy orders with JSON items
                $jsonItems = json_decode($order->getRawOriginal('items'), true) ?? [];
                if (is_array($jsonItems) && count($jsonItems) > 0) {
                    $order->items = collect($jsonItems)->map(function ($item) {
                        $product = \App\Models\Product::find($item['product_id'] ?? null);
                        if ($product) {
                            $item['product_name'] = $product->name;
                        }
                        return $item;
                    })->toArray();
                } else {
                    $order->items = [];
                }
            }
        });

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['user', 'buyer', 'organization', 'orderItems.employee']);
        
        // Use buyer for B2B orders, fallback to user for legacy
        if (!$order->user && $order->buyer) {
            $order->user = $order->buyer;
        }
        
        // Load items from order_items table for B2B orders
        if ($order->orderItems && $order->orderItems->count() > 0) {
            $order->items = $order->orderItems->map(function ($item) {
                $product = \App\Models\Product::where('name', $item->product_name)->first();
                return [
                    'product_id' => $product ? $product->id : 0,
                    'product_name' => $item->product_name,
                    'product_description' => $product ? $product->description : '',
                    'product_image' => $product ? $product->photo_url : '',
                    'quantity' => $item->quantity,
                    'price' => $product ? $product->price : 0,
                ];
            })->toArray();
        } else {
            // For legacy orders with JSON items
            $jsonItems = json_decode($order->getRawOriginal('items'), true) ?? [];
            if (is_array($jsonItems) && count($jsonItems) > 0) {
                $order->items = collect($jsonItems)->map(function ($item) {
                    $product = \App\Models\Product::find($item['product_id'] ?? null);
                    if ($product) {
                        $item['product_name'] = $product->name;
                        $item['product_description'] = $product->description;
                        $item['product_image'] = $product->photo_url;
                    }
                    return $item;
                })->toArray();
            } else {
                $order->items = [];
            }
        }

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
