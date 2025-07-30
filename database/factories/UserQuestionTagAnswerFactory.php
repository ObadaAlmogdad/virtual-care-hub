<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Patient;
use App\Models\QuestionMedicalTag;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserQuestionTagAnswer>
 */
class UserQuestionTagAnswerFactory extends Factory
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
            'question_medical_tag_id' => QuestionMedicalTag::factory(),
            'answer' => $this->faker->word,
        ];
    }
}
