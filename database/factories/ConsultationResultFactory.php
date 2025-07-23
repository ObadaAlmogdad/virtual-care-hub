<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Consultation;
use App\Models\UserQuestionTagAnswer;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConsultationResult>
 */
class ConsultationResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'consultation_id' => Consultation::factory(),
            'user_question_tag_answer_id' => UserQuestionTagAnswer::factory(),
            'replayOfDoctor' => $this->faker->sentence,
            'accepted' => $this->faker->boolean,
        ];
    }
}
