<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\Invite;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CollaborativeOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_and_employee_collaborate_on_order(): void
    {
        // 1. Create organization and buyer
        $organization = Organization::factory()->create();
        $buyer = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => 'buyer',
            'telegram_id' => '1001',
            'username' => 'buyer_user',
            'first_name' => 'Закупщик',
            'last_name' => 'Иванов',
        ]);

        // 2. Buyer generates an invite for employee
        $inviteToken = $this->generateInvite($buyer, $organization);

        // 3. Employee registers via invite
        $employee = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => 'employee',
            'telegram_id' => '1002',
            'username' => 'employee_user',
            'first_name' => 'Сотрудник',
            'last_name' => 'Петров',
        ]);

        // Mark invite as used
        $invite = Invite::where('token', $inviteToken)->first();
        $invite->update(['used_at' => now()]);

        // 4. Create some products
        $product1 = Product::factory()->create(['name' => 'Микрозелень укропа', 'price' => 150]);
        $product2 = Product::factory()->create(['name' => 'Микрозелень петрушки', 'price' => 140]);
        $product3 = Product::factory()->create(['name' => 'Микрозелень кинзы', 'price' => 170]);

        // 5. Buyer creates a draft order
        $buyerToken = $this->generateTestToken($buyer);
        $createOrderResponse = $this->postJson('/api/b2b/orders', [], [
            'Authorization' => 'Bearer ' . $buyerToken,
        ]);

        $createOrderResponse->assertStatus(201);
        $orderId = $createOrderResponse->json('data.id');

        // 6. Employee adds first product to the order
        $employeeToken = $this->generateTestToken($employee);
        $addItem1Response = $this->postJson("/api/b2b/orders/{$orderId}/items", [
            'product_id' => $product1->id,
            'quantity' => 2,
        ], [
            'Authorization' => 'Bearer ' . $employeeToken,
        ]);

        $addItem1Response->assertStatus(201);

        // 7. Buyer adds second product to the same order
        $addItem2Response = $this->postJson("/api/b2b/orders/{$orderId}/items", [
            'product_id' => $product2->id,
            'quantity' => 1,
        ], [
            'Authorization' => 'Bearer ' . $buyerToken,
        ]);

        $addItem2Response->assertStatus(201);

        // 8. Employee adds third product
        $addItem3Response = $this->postJson("/api/b2b/orders/{$orderId}/items", [
            'product_id' => $product3->id,
            'quantity' => 3,
        ], [
            'Authorization' => 'Bearer ' . $employeeToken,
        ]);

        $addItem3Response->assertStatus(201);

        // 9. Get order details to verify all items
        $getOrderResponse = $this->getJson("/api/b2b/orders/{$orderId}", [
            'Authorization' => 'Bearer ' . $buyerToken,
        ]);

        $getOrderResponse->assertStatus(200);
        $orderData = $getOrderResponse->json('data');

        // Verify order has all items from both users
        $this->assertCount(3, $orderData['order_items']);

        // Verify items and who added them
        $item1 = collect($orderData['order_items'])->where('product_name', 'Микрозелень укропа')->first();
        $this->assertNotNull($item1);
        $this->assertEquals('2.00', $item1['quantity']);
        $this->assertEquals($employee->id, $item1['employee_id']);

        $item2 = collect($orderData['order_items'])->where('product_name', 'Микрозелень петрушки')->first();
        $this->assertNotNull($item2);
        $this->assertEquals('1.00', $item2['quantity']);

        $item3 = collect($orderData['order_items'])->where('product_name', 'Микрозелень кинзы')->first();
        $this->assertNotNull($item3);
        $this->assertEquals('3.00', $item3['quantity']);
        $this->assertEquals($employee->id, $item3['employee_id']);

        // 10. Buyer submits the order
        $submitOrderResponse = $this->postJson("/api/b2b/orders/{$orderId}/submit", [], [
            'Authorization' => 'Bearer ' . $buyerToken,
        ]);

        $submitOrderResponse->assertStatus(200);
        $submittedOrder = $submitOrderResponse->json('data');

        // Verify order is submitted
        $this->assertEquals('submitted', $submittedOrder['status']);
        $this->assertNotNull($submittedOrder['submitted_at']);
        $this->assertEquals($buyer->id, $submittedOrder['buyer_id']);

        // Calculate expected total: (2 * 150) + (1 * 140) + (3 * 170) = 300 + 140 + 510 = 950
        $expectedTotal = (2 * 150) + (1 * 140) + (3 * 170);
        $this->assertEquals(number_format($expectedTotal, 2, '.', ''), $submittedOrder['total']);

        // 11. Verify order appears in admin panel
        $adminOrdersResponse = $this->getJson('/api/admin/orders');
        $adminOrdersResponse->assertStatus(200);
        
        $adminOrder = collect($adminOrdersResponse->json('data'))
            ->where('id', $orderId)
            ->first();
        
        $this->assertNotNull($adminOrder);
        $this->assertEquals('submitted', $adminOrder['status']);
        $this->assertCount(3, $adminOrder['items']);
    }

    public function test_employee_cannot_submit_order(): void
    {
        // Create organization and users
        $organization = Organization::factory()->create();
        $buyer = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => 'buyer',
        ]);
        $employee = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => 'employee',
        ]);

        // Buyer creates order
        $buyerToken = $this->generateTestToken($buyer);
        $order = Order::create([
            'organization_id' => $organization->id,
            'user_id' => $buyer->telegram_id ?? 0,
            'status' => 'draft',
            'items' => [],
            'total' => 0,
        ]);

        // Employee tries to submit - should fail
        $employeeToken = $this->generateTestToken($employee);
        $response = $this->postJson("/api/b2b/orders/{$order->id}/submit", [], [
            'Authorization' => 'Bearer ' . $employeeToken,
        ]);

        $response->assertStatus(403);
    }

    private function generateInvite(User $creator, Organization $organization): string
    {
        $token = \Str::random(32);
        Invite::create([
            'organization_id' => $organization->id,
            'token' => $token,
            'created_by' => $creator->id,
            'expires_at' => now()->addDays(7),
        ]);
        return $token;
    }

    private function generateTestToken(User $user): string
    {
        $payload = [
            'iss' => url('/'),
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => $user->id,
            'telegram_id' => $user->telegram_id,
            'role' => $user->role,
        ];

        return \Firebase\JWT\JWT::encode($payload, config('app.key'), 'HS256');
    }
}

