<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'legal_name' => 'ООО ' . $this->faker->company(),
            'inn' => $this->faker->numerify('##########'),
            'kpp' => $this->faker->numerify('#########'),
            'ogrn' => $this->faker->numerify('############'),
            'address_legal' => $this->faker->address(),
            'address_actual' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'comment' => $this->faker->sentence(),
            'created_by' => User::factory(),
        ];
    }
}

