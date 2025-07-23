<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicalTag>
 */
class MedicalTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'name_ar' => $this->faker->word(),
            'description' => $this->faker->optional()->sentence(),
            'icon' => $this->faker->optional()->imageUrl(),
            'is_active' => $this->faker->boolean(),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
