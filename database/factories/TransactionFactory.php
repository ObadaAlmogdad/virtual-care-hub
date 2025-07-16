<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Wallet;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'type' => $this->faker->randomElement(['charge', 'payment', 'refund']),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'reference_id' => $this->faker->optional()->uuid,
            'reference_type' => $this->faker->optional()->word,
            'description' => $this->faker->optional()->sentence,
        ];
    }
}
