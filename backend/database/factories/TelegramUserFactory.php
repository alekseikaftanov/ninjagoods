<?php

namespace Database\Factories;

use App\Models\TelegramUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class TelegramUserFactory extends Factory
{
    protected $model = TelegramUser::class;

    public function definition(): array
    {
        return [
            'telegram_id' => 'user_' . $this->faker->numerify('#########'),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}

