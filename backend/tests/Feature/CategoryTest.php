<?php

namespace Tests\Feature;

use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест создания категории
     */
    public function test_create_category(): void
    {
        $categoryData = [
            'name' => 'Микрозелень',
        ];

        $response = $this->postJson('/api/admin/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Микрозелень',
        ]);
    }

    /**
     * Тест создания категории с родительской категорией
     */
    public function test_create_category_with_parent(): void
    {
        $parentCategory = Category::factory()->create([
            'name' => 'Родительская категория',
        ]);

        $categoryData = [
            'name' => 'Подкатегория',
            'parent_id' => $parentCategory->id,
        ];

        $response = $this->postJson('/api/admin/categories', $categoryData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('categories', [
            'name' => 'Подкатегория',
            'parent_id' => $parentCategory->id,
        ]);
    }

    /**
     * Тест валидации при создании категории
     */
    public function test_create_category_validation_error(): void
    {
        $response = $this->postJson('/api/admin/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Тест удаления категории
     */
    public function test_delete_category(): void
    {
        $category = Category::factory()->create([
            'name' => 'Тестовая категория',
        ]);

        $categoryId = $category->id;

        $response = $this->deleteJson("/api/admin/categories/{$categoryId}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Категория удалена',
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $categoryId,
        ]);
    }

    /**
     * Тест удаления несуществующей категории
     */
    public function test_delete_nonexistent_category(): void
    {
        $response = $this->deleteJson('/api/admin/categories/99999');

        $response->assertStatus(404);
    }
}

