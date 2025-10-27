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
        // Единый URL для всех товаров - качественное фото микрозелени
        $microgreensPhoto = 'https://img.freepik.com/free-photo/composition-delicious-exotic-bananas_23-2149090914.jpg?semt=ais_hybrid&w=740&q=80';
        
        $products = [
            // Листовая зелень
            [
                'name' => 'Микрозелень салата Айсберг',
                'photo_url' => $microgreensPhoto,
                'description' => 'Нежная микрозелень салата Айсберг с хрустящими листочками. Богата витаминами A, C и K.',
                'unit' => 'штуки',
                'price' => 150.00,
                'min_order' => 1.00,
                'category_id' => 1, // Листовая зелень
            ],
            [
                'name' => 'Микрозелень шпината',
                'photo_url' => $microgreensPhoto,
                'description' => 'Питательная микрозелень шпината с высоким содержанием железа и фолиевой кислоты.',
                'unit' => 'штуки',
                'price' => 180.00,
                'min_order' => 1.00,
                'category_id' => 1, // Листовая зелень
            ],
            [
                'name' => 'Микрозелень рукколы',
                'photo_url' => $microgreensPhoto,
                'description' => 'Острая микрозелень рукколы с пикантным ореховым вкусом. Отлично подходит для салатов.',
                'unit' => 'штуки',
                'price' => 160.00,
                'min_order' => 1.00,
                'category_id' => 1, // Листовая зелень
            ],
            [
                'name' => 'Микрозелень мангольда',
                'photo_url' => $microgreensPhoto,
                'description' => 'Яркая микрозелень мангольда с нежным вкусом. Богата антиоксидантами.',
                'unit' => 'штуки',
                'price' => 170.00,
                'min_order' => 1.00,
                'category_id' => 1, // Листовая зелень
            ],
            
            // Корнеплоды
            [
                'name' => 'Микрозелень редиса',
                'photo_url' => $microgreensPhoto,
                'description' => 'Острая микрозелень редиса с пикантным вкусом. Отлично подходит для салатов и сэндвичей.',
                'unit' => 'штуки',
                'price' => 120.00,
                'min_order' => 1.00,
                'category_id' => 2, // Корнеплоды
            ],
            [
                'name' => 'Микрозелень свеклы',
                'photo_url' => $microgreensPhoto,
                'description' => 'Сладкая микрозелень свеклы с земляным вкусом. Богата нитратами и антиоксидантами.',
                'unit' => 'штуки',
                'price' => 140.00,
                'min_order' => 1.00,
                'category_id' => 2, // Корнеплоды
            ],
            [
                'name' => 'Микрозелень моркови',
                'photo_url' => $microgreensPhoto,
                'description' => 'Сладкая микрозелень моркови с ярким оранжевым цветом. Источник бета-каротина.',
                'unit' => 'штуки',
                'price' => 130.00,
                'min_order' => 1.00,
                'category_id' => 2, // Корнеплоды
            ],
            
            // Травы
            [
                'name' => 'Микрозелень базилика',
                'photo_url' => $microgreensPhoto,
                'description' => 'Ароматная микрозелень базилика с интенсивным вкусом. Идеальна для итальянских блюд.',
                'unit' => 'штуки',
                'price' => 200.00,
                'min_order' => 1.00,
                'category_id' => 3, // Травы
            ],
            [
                'name' => 'Микрозелень кориандра',
                'photo_url' => $microgreensPhoto,
                'description' => 'Свежая микрозелень кориандра с цитрусовым ароматом. Отлично дополняет азиатские блюда.',
                'unit' => 'штуки',
                'price' => 160.00,
                'min_order' => 1.00,
                'category_id' => 3, // Травы
            ],
            [
                'name' => 'Микрозелень укропа',
                'photo_url' => $microgreensPhoto,
                'description' => 'Ароматная микрозелень укропа с нежным анисовым вкусом. Отлично подходит к рыбе.',
                'unit' => 'штуки',
                'price' => 150.00,
                'min_order' => 1.00,
                'category_id' => 3, // Травы
            ],
            [
                'name' => 'Микрозелень петрушки',
                'photo_url' => $microgreensPhoto,
                'description' => 'Свежая микрозелень петрушки с ярким зеленым цветом. Богата витамином C.',
                'unit' => 'штуки',
                'price' => 140.00,
                'min_order' => 1.00,
                'category_id' => 3, // Травы
            ],
            [
                'name' => 'Микрозелень кинзы',
                'photo_url' => $microgreensPhoto,
                'description' => 'Пряная микрозелень кинзы с цитрусовым ароматом. Идеальна для мексиканской кухни.',
                'unit' => 'штуки',
                'price' => 170.00,
                'min_order' => 1.00,
                'category_id' => 3, // Травы
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
