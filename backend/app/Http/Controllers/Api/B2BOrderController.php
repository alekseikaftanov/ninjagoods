<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class B2BOrderController extends Controller
{
    /**
     * Get all orders for organization
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Order::where('organization_id', $user->organization_id);
        
        // Employees can only see their own draft orders
        if ($user->isEmployee()) {
            $query->where(function ($q) use ($user) {
                $q->where('status', '!=', 'draft')
                    ->orWhere(function ($subQ) use ($user) {
                        $subQ->where('status', 'draft')
                            ->whereExists(function ($existsQuery) use ($user) {
                                $existsQuery->select(\DB::raw(1))
                                    ->from('order_items')
                                    ->whereColumn('order_items.order_id', 'orders.id')
                                    ->where('order_items.employee_id', $user->id);
                            });
                    });
            });
        }
        
        $orders = $query->with(['buyer', 'orderItems.employee'])->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Create draft order
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'No organization found',
            ], 400);
        }

        $orderData = [
            'organization_id' => $user->organization_id,
            'user_id' => $user->telegram_id ?? 0, // Legacy field
            'status' => 'draft',
            'items' => [],
            'total' => 0,
        ];

        if ($user->isBuyer()) {
            $orderData['buyer_id'] = $user->id;
        }

        $order = Order::create($orderData);

        return response()->json([
            'success' => true,
            'data' => $order,
        ], 201);
    }

    /**
     * Get order details
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Check access
        if ($order->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        if ($user->isEmployee() && $order->status === 'draft') {
            // Employee can only see draft orders they contributed to
            $hasItems = $order->orderItems()->where('employee_id', $user->id)->exists();
            if (!$hasItems) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden',
                ], 403);
            }
        }

        $order->load(['buyer', 'orderItems.employee']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Add item to order
     */
    public function addItem(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Check access
        if ($order->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        if ($order->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify submitted order',
            ], 400);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'employee_id' => $user->id,
            'product_name' => $product->name,
            'quantity' => $validated['quantity'],
            'comment' => $validated['comment'] ?? null,
        ]);

        // Recalculate total
        $total = $order->orderItems()
            ->join('products', 'order_items.product_name', '=', 'products.name')
            ->selectRaw('SUM(order_items.quantity * products.price) as total')
            ->value('total');

        $order->total = $total;
        $order->save();

        return response()->json([
            'success' => true,
            'data' => $orderItem,
        ], 201);
    }

    /**
     * Submit order (buyer only)
     */
    public function submit(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if (!$user->isBuyer()) {
            return response()->json([
                'success' => false,
                'message' => 'Only buyers can submit orders',
            ], 403);
        }

        if ($order->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        if ($order->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Order already submitted',
            ], 400);
        }

        $order->status = 'submitted';
        $order->submitted_at = now();
        $order->buyer_id = $user->id;
        $order->save();

        // Update legacy items JSON for admin panel
        $items = $order->orderItems()->get()->map(function ($item) use ($order) {
            $product = Product::where('name', $item->product_name)->first();
            return [
                'product_id' => $product ? $product->id : 0,
                'quantity' => $item->quantity,
                'price' => $product ? $product->price : 0,
            ];
        })->toArray();

        $order->items = $items;
        $order->save();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Delete order item
     */
    public function deleteItem(Request $request, Order $order, OrderItem $item): JsonResponse
    {
        $user = $request->user();

        if ($order->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        if ($order->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify submitted order',
            ], 400);
        }

        // Employee can only delete their own items
        if ($user->isEmployee() && $item->employee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Can only delete own items',
            ], 403);
        }

        $item->delete();

        // Recalculate total
        $total = $order->orderItems()
            ->join('products', 'order_items.product_name', '=', 'products.name')
            ->selectRaw('SUM(order_items.quantity * products.price) as total')
            ->value('total');

        $order->total = $total ?? 0;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted',
        ]);
    }
}
