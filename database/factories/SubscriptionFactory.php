<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Plan;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
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
            'plan_id' => Plan::factory(),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->optional()->date(),
            'status' => $this->faker->randomElement(['active', 'expired', 'cancelled']),
            'payment_method' => $this->faker->randomElement(['wallet', 'stripe']),
            'payment_id' => null,
        ];
    }
}
