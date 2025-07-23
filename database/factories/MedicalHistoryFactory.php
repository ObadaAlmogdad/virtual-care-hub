<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Patient;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicalHistory>
 */
class MedicalHistoryFactory extends Factory
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
            'general_diseases' => json_encode([$this->faker->word]),
            'chronic_diseases' => json_encode([$this->faker->word]),
            'surgeries' => $this->faker->optional()->sentence,
            'allergies' => $this->faker->optional()->sentence,
            'permanent_medications' => $this->faker->optional()->sentence,
            'medical_documents_path' => $this->faker->optional()->filePath(),
        ];
    }
}
