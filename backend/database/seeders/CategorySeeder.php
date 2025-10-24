<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Основные категории микрозелени
        $categories = [
            [
                'name' => 'Листовая зелень',
                'parent_id' => null,
            ],
            [
                'name' => 'Корнеплоды',
                'parent_id' => null,
            ],
            [
                'name' => 'Травы',
                'parent_id' => null,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Подкатегории
        $subcategories = [
            [
                'name' => 'Салаты',
                'parent_id' => 1,
            ],
            [
                'name' => 'Шпинат',
                'parent_id' => 1,
            ],
            [
                'name' => 'Редис',
                'parent_id' => 2,
            ],
            [
                'name' => 'Базилик',
                'parent_id' => 3,
            ],
            [
                'name' => 'Кориандр',
                'parent_id' => 3,
            ],
        ];

        foreach ($subcategories as $subcategory) {
            Category::create($subcategory);
        }
    }
}
