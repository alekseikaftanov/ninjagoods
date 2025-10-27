<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'photo_url' => $this->faker->imageUrl(),
            'description' => $this->faker->sentence(),
            'unit' => $this->faker->randomElement(['штуки', 'килограммы']),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'min_order' => $this->faker->randomFloat(2, 0.1, 10),
            'category_id' => Category::factory(),
        ];
    }
}

