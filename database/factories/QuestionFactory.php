<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MedicalTag;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'specialty_id' => MedicalTag::factory(),
            'parent_question_id' => null,
            'parent_answer_value' => null,
            'question_text' => $this->faker->sentence(),
            'isActive' => $this->faker->boolean(),
        ];
    }
}
