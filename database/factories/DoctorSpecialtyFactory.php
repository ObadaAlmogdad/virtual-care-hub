<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Doctor;
use App\Models\MedicalTag;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DoctorSpecialty>
 */
class DoctorSpecialtyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'medical_tag_id' => MedicalTag::factory(),
            'start_time' => $this->faker->dateTime,
            'end_time' => $this->faker->dateTime,
            'yearOfExper' => $this->faker->year,
            'photo' => json_encode([$this->faker->imageUrl()]),
            'consultation_fee' => $this->faker->randomFloat(2, 50, 500),
            'description' => $this->faker->optional()->sentence,
            'is_active' => $this->faker->boolean,
        ];
    }
}
