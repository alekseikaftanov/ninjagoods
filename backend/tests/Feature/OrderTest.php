<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\TelegramUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест создания заказа
     */
    public function test_create_order(): void
    {
        $user = TelegramUser::factory()->create();
        $category = Category::factory()->create();
        
        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
        ]);
        
        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 50.00,
        ]);

        $orderData = [
            'user_id' => $user->id,
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 3,
                ],
            ],
            'comment' => 'Тестовый заказ',
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'user_id',
                    'items',
                    'total',
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Проверяем, что заказ создан в базе
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
        ]);

        // Проверяем правильность расчета общей суммы
        // product1: 100.00 * 2 = 200.00
        // product2: 50.00 * 3 = 150.00
        // Итого: 350.00
        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals(350.00, $order->total);
    }

    /**
     * Тест создания заказа без комментария
     */
    public function test_create_order_without_comment(): void
    {
        $user = TelegramUser::factory()->create();
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
        ]);

        $orderData = [
            'user_id' => $user->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Тест валидации при создании заказа
     */
    public function test_create_order_validation_error(): void
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'user_id',
                'items',
            ]);
    }

    /**
     * Тест создания заказа с несуществующим пользователем
     */
    public function test_create_order_with_nonexistent_user(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $orderData = [
            'user_id' => 99999, // Несуществующий пользователь
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    /**
     * Тест создания заказа с несуществующим товаром
     */
    public function test_create_order_with_nonexistent_product(): void
    {
        $user = TelegramUser::factory()->create();

        $orderData = [
            'user_id' => $user->id,
            'items' => [
                [
                    'product_id' => 99999, // Несуществующий товар
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    /**
     * Тест расчета общей суммы заказа с несколькими товарами
     */
    public function test_order_total_calculation_with_multiple_products(): void
    {
        $user = TelegramUser::factory()->create();
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 130.00,
        ]);

        $orderData = [
            'user_id' => $user->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        // Проверяем правильность расчета: 130.00 * 5 = 650.00
        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals(650.00, $order->total);
    }

    /**
     * Тест создания заказа с дробным количеством товара
     */
    public function test_create_order_with_decimal_quantity(): void
    {
        $user = TelegramUser::factory()->create();
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
        ]);

        $orderData = [
            'user_id' => $user->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2.5,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        // Проверяем правильность расчета: 100.00 * 2.5 = 250.00
        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals(250.00, $order->total);
    }
}

