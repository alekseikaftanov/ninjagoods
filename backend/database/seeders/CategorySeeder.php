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
        $leafyGreens = Category::create([
            'name' => 'Листовая зелень',
            'parent_id' => null,
        ]);

        $roots = Category::create([
            'name' => 'Корнеплоды',
            'parent_id' => null,
        ]);

        $herbs = Category::create([
            'name' => 'Травы',
            'parent_id' => null,
        ]);

        // Подкатегории
        Category::create([
            'name' => 'Салаты',
            'parent_id' => $leafyGreens->id,
        ]);

        Category::create([
            'name' => 'Шпинат',
            'parent_id' => $leafyGreens->id,
        ]);

        Category::create([
            'name' => 'Редис',
            'parent_id' => $roots->id,
        ]);

        Category::create([
            'name' => 'Базилик',
            'parent_id' => $herbs->id,
        ]);

        Category::create([
            'name' => 'Кориандр',
            'parent_id' => $herbs->id,
        ]);
    }
}
