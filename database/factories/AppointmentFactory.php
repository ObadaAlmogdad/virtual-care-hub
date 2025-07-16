<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Patient;
use App\Models\Doctor;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
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
            'doctor_note' => $this->faker->optional()->sentence,
            'user_note' => $this->faker->optional()->sentence,
            'date' => $this->faker->date(),
            'day' => $this->faker->dayOfWeek,
            'time' => $this->faker->time(),
        ];
    }
}
