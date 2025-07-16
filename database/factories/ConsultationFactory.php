<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\MedicalTag;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Consultation>
 */
class ConsultationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'medical_tag_id' => MedicalTag::factory(),
            'isSpecial' => $this->faker->boolean,
            'problem' => $this->faker->sentence,
            'media' => $this->faker->optional()->imageUrl(),
            'isAnonymous' => $this->faker->boolean,
            'fee' => $this->faker->randomFloat(2, 50, 500),
            'status' => $this->faker->randomElement(['pending', 'accepted', 'rejected', 'scheduled', 'completed']),
            'scheduled_at' => $this->faker->optional()->dateTime,
            'reminder_before_minutes' => $this->faker->numberBetween(10, 120),
        ];
    }
}
