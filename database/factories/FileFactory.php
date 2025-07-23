<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => $this->faker->filePath(),
            'origanName' => $this->faker->word() . '.' . $this->faker->fileExtension(),
            'size' => $this->faker->numberBetween(1000, 1000000),
            'extension' => $this->faker->fileExtension(),
        ];
    }
}
