<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Микрозелень салата Айсберг',
                'photo_url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=400&fit=crop&crop=center',
                'description' => 'Нежная микрозелень салата Айсберг с хрустящими листочками. Богата витаминами A, C и K.',
                'unit' => 'штуки',
                'price' => 150.00,
                'min_order' => 1.00,
                'category_id' => 1, // Листовая зелень
            ],
            [
                'name' => 'Микрозелень шпината',
                'photo_url' => 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=400&h=400&fit=crop&crop=center',
                'description' => 'Питательная микрозелень шпината с высоким содержанием железа и фолиевой кислоты.',
                'unit' => 'штуки',
                'price' => 180.00,
                'min_order' => 1.00,
                'category_id' => 1, // Листовая зелень
            ],
            [
                'name' => 'Микрозелень редиса',
                'photo_url' => 'https://images.unsplash.com/photo-1592924357228-91f4c0c0b8cc?w=400&h=400&fit=crop&crop=center',
                'description' => 'Острая микрозелень редиса с пикантным вкусом. Отлично подходит для салатов и сэндвичей.',
                'unit' => 'штуки',
                'price' => 120.00,
                'min_order' => 1.00,
                'category_id' => 2, // Корнеплоды
            ],
            [
                'name' => 'Микрозелень базилика',
                'photo_url' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=400&h=400&fit=crop&crop=center',
                'description' => 'Ароматная микрозелень базилика с интенсивным вкусом. Идеальна для итальянских блюд.',
                'unit' => 'штуки',
                'price' => 200.00,
                'min_order' => 1.00,
                'category_id' => 3, // Травы
            ],
            [
                'name' => 'Микрозелень кориандра',
                'photo_url' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=400&h=400&fit=crop&crop=center',
                'description' => 'Свежая микрозелень кориандра с цитрусовым ароматом. Отлично дополняет азиатские блюда.',
                'unit' => 'штуки',
                'price' => 160.00,
                'min_order' => 1.00,
                'category_id' => 3, // Травы
            ],
            [
                'name' => 'Бананы',
                'photo_url' => 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=400&h=400&fit=crop&crop=center',
                'description' => 'Свежие спелые бананы с нежной мякотью. Богаты калием и витаминами.',
                'unit' => 'килограммы',
                'price' => 80.00,
                'min_order' => 0.5,
                'category_id' => 1, // Листовая зелень (временно)
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
