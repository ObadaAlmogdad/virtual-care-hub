<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
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
            'bio' => $this->faker->text(100),
            'activatePoint' => $this->faker->word(),
            'rating' => $this->faker->randomFloat(1, 1, 5),
            'work_days' => json_encode([$this->faker->dayOfWeek]),
            'work_time_in' => $this->faker->time(),
            'work_time_out' => $this->faker->time(),
            'certificate_images' => json_encode([$this->faker->imageUrl()]),
            'time_for_waiting' => $this->faker->numberBetween(5, 60),
            'facebook_url' => $this->faker->optional()->url,
            'instagram_url' => $this->faker->optional()->url,
            'twitter_url' => $this->faker->optional()->url,
            'address' => $this->faker->address(),
        ];
    }
}
