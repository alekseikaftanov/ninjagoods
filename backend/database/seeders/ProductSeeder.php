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
                'photo_url' => 'https://via.placeholder.com/300x200/90EE90/000000?text=Микрозелень+Айсберг',
                'description' => 'Нежная микрозелень салата Айсберг с хрустящими листочками. Богата витаминами A, C и K.',
                'unit' => 'штуки',
                'price' => 150.00,
                'min_order' => 1.00,
                'category_id' => 4, // Салаты
            ],
            [
                'name' => 'Микрозелень шпината',
                'photo_url' => 'https://via.placeholder.com/300x200/228B22/FFFFFF?text=Микрозелень+Шпинат',
                'description' => 'Питательная микрозелень шпината с высоким содержанием железа и фолиевой кислоты.',
                'unit' => 'штуки',
                'price' => 180.00,
                'min_order' => 1.00,
                'category_id' => 5, // Шпинат
            ],
            [
                'name' => 'Микрозелень редиса',
                'photo_url' => 'https://via.placeholder.com/300x200/FF6347/FFFFFF?text=Микрозелень+Редис',
                'description' => 'Острая микрозелень редиса с пикантным вкусом. Отлично подходит для салатов и сэндвичей.',
                'unit' => 'штуки',
                'price' => 120.00,
                'min_order' => 1.00,
                'category_id' => 6, // Редис
            ],
            [
                'name' => 'Микрозелень базилика',
                'photo_url' => 'https://via.placeholder.com/300x200/32CD32/FFFFFF?text=Микрозелень+Базилик',
                'description' => 'Ароматная микрозелень базилика с интенсивным вкусом. Идеальна для итальянских блюд.',
                'unit' => 'штуки',
                'price' => 200.00,
                'min_order' => 1.00,
                'category_id' => 7, // Базилик
            ],
            [
                'name' => 'Микрозелень кориандра',
                'photo_url' => 'https://via.placeholder.com/300x200/9ACD32/FFFFFF?text=Микрозелень+Кориандр',
                'description' => 'Свежая микрозелень кориандра с цитрусовым ароматом. Отлично дополняет азиатские блюда.',
                'unit' => 'штуки',
                'price' => 160.00,
                'min_order' => 1.00,
                'category_id' => 8, // Кориандр
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
