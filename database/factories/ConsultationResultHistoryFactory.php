<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ConsultationResult;
use App\Models\MedicalHistory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConsultationResultHistory>
 */
class ConsultationResultHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'consultation_result_id' => ConsultationResult::factory(),
            'medical_history_id' => MedicalHistory::factory(),
        ];
    }
}
