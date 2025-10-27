<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class B2BOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_draft_order(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => 'buyer',
        ]);

        $token = $this->generateTestToken($user);

        $response = $this->postJson('/api/b2b/orders', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'organization_id',
                    'status',
                    'total',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'organization_id' => $organization->id,
            'status' => 'draft',
            'total' => 0,
        ]);
    }

    public function test_add_item_to_order(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => 'buyer',
        ]);
        $product = Product::factory()->create();

        $token = $this->generateTestToken($user);

        $order = Order::create([
            'organization_id' => $organization->id,
            'user_id' => $user->telegram_id ?? 0,
            'status' => 'draft',
            'items' => [],
            'total' => 0,
        ]);

        $response = $this->postJson("/api/b2b/orders/{$order->id}/items", [
            'product_id' => $product->id,
            'quantity' => 2,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'employee_id' => $user->id,
            'product_name' => $product->name,
            'quantity' => 2,
        ]);
    }

    public function test_submit_order(): void
    {
        $organization = Organization::factory()->create();
        $buyer = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => 'buyer',
        ]);
        $product = Product::factory()->create([
            'price' => 100,
        ]);

        $token = $this->generateTestToken($buyer);

        $order = Order::create([
            'organization_id' => $organization->id,
            'user_id' => $buyer->telegram_id ?? 0,
            'status' => 'draft',
            'items' => [],
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'employee_id' => $buyer->id,
            'product_name' => $product->name,
            'quantity' => 3,
        ]);

        $response = $this->postJson("/api/b2b/orders/{$order->id}/submit", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'submitted',
            'buyer_id' => $buyer->id,
        ]);

        $this->assertNotNull(Order::find($order->id)->submitted_at);
    }

    public function test_employee_cannot_submit_order(): void
    {
        $organization = Organization::factory()->create();
        $employee = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => 'employee',
        ]);

        $token = $this->generateTestToken($employee);

        $order = Order::create([
            'organization_id' => $organization->id,
            'user_id' => $employee->telegram_id ?? 0,
            'status' => 'draft',
            'items' => [],
            'total' => 0,
        ]);

        $response = $this->postJson("/api/b2b/orders/{$order->id}/submit", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }

    public function test_get_all_orders(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => 'buyer',
        ]);

        $token = $this->generateTestToken($user);

        Order::create([
            'organization_id' => $organization->id,
            'user_id' => $user->telegram_id ?? 0,
            'status' => 'draft',
            'items' => [],
            'total' => 0,
        ]);

        $response = $this->getJson('/api/b2b/orders', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data');
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

