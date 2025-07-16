<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Consultation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'consultation_id' => Consultation::factory(),
            'header' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'type' => $this->faker->randomElement(['pending', 'in_progress', 'resolved']),
            'media' => json_encode([$this->faker->imageUrl()]),
            'answer' => $this->faker->optional()->sentence,
        ];
    }
}
