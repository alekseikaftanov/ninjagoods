<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест создания товара
     */
    public function test_create_product(): void
    {
        $category = Category::factory()->create();

        $productData = [
            'name' => 'Микрозелень моркови',
            'photo_url' => 'https://example.com/photo.jpg',
            'description' => 'Свежая микрозелень моркови',
            'unit' => 'штуки',
            'price' => 130.00,
            'min_order' => 1,
            'category_id' => $category->id,
        ];

        $response = $this->postJson('/api/admin/products', $productData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'photo_url',
                    'description',
                    'unit',
                    'price',
                    'min_order',
                    'category_id',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Микрозелень моркови',
            'category_id' => $category->id,
        ]);
    }

    /**
     * Тест валидации при создании товара
     */
    public function test_create_product_validation_error(): void
    {
        $response = $this->postJson('/api/admin/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'photo_url',
                'description',
                'unit',
                'price',
                'min_order',
                'category_id',
            ]);
    }

    /**
     * Тест редактирования свойств товара
     */
    public function test_update_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $updateData = [
            'name' => 'Обновленное название',
            'photo_url' => $product->photo_url,
            'description' => 'Обновленное описание',
            'unit' => 'килограммы',
            'price' => 150.00,
            'min_order' => 2,
            'category_id' => $category->id,
        ];

        $response = $this->putJson("/api/admin/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Обновленное название',
            'description' => 'Обновленное описание',
            'unit' => 'килограммы',
            'price' => 150.00,
            'min_order' => 2,
        ]);
    }

    /**
     * Тест редактирования цены товара
     */
    public function test_update_product_price(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
        ]);

        $updateData = [
            'name' => $product->name,
            'photo_url' => $product->photo_url,
            'description' => $product->description,
            'unit' => $product->unit,
            'price' => 250.00,
            'min_order' => $product->min_order,
            'category_id' => $category->id,
        ];

        $response = $this->putJson("/api/admin/products/{$product->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'price' => 250.00,
        ]);
    }

    /**
     * Тест валидации при редактировании товара
     */
    public function test_update_product_validation_error(): void
    {
        $product = Product::factory()->create();

        $response = $this->putJson("/api/admin/products/{$product->id}", [
            'name' => '', // Пустое имя
            'price' => -100, // Отрицательная цена
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price']);
    }

    /**
     * Тест удаления товара
     */
    public function test_delete_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $productId = $product->id;

        $response = $this->deleteJson("/api/admin/products/{$productId}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Товар удален',
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $productId,
        ]);
    }

    /**
     * Тест удаления несуществующего товара
     */
    public function test_delete_nonexistent_product(): void
    {
        $response = $this->deleteJson('/api/admin/products/99999');

        $response->assertStatus(404);
    }
}

