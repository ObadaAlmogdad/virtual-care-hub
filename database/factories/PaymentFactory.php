<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\Consultation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'doctor_id' => User::factory(),
            'consultation_id' => Consultation::factory(),
            'stripe_payment_intent_id' => 'pi_' . $this->faker->unique()->randomNumber(8),
            'amount' => $this->faker->numberBetween(100, 1000),
            'fee' => function (array $attributes) {
                return round($attributes['amount'] * 0.05);
            },
            'net_amount' => function (array $attributes) {
                return $attributes['amount'] - round($attributes['amount'] * 0.05);
            },
            'status' => $this->faker->randomElement(['pending', 'succeeded', 'failed']),
            'is_refunded' => false,
            'refunded_at' => null,
            'refund_reason' => null,
        ];
    }

    public function succeeded()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'succeeded',
            ];
        });
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
            ];
        });
    }

    public function refunded()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'succeeded',
                'is_refunded' => true,
                'refunded_at' => now(),
                'refund_reason' => $this->faker->sentence,
            ];
        });
    }
} 