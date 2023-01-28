<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class PatientsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name'              => fake()->name(),
            'birth_date'        => fake()->dateTimeBetween('-30 years', '-25 years'),
            'address'           => fake()->address(),
            'phone_number'      => "813" . fake()->randomNumber(8, true),
            'weight'            => fake()->numberBetween(50, 90),
            'height'            => fake()->numberBetween(155, 178),
            'additional_note'   => fake()->paragraph(),
        ];
    }
}
