<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MedicalTag;
use App\Models\Question;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuestionMedicalTag>
 */
class QuestionMedicalTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medical_tag_id' => MedicalTag::factory(),
            'question_id' => Question::factory(),
        ];
    }
}
