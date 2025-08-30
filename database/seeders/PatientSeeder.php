<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\User;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample patients
        $patients = [
            [
                'user_id' => null, // Will be set after user creation
                'fakeName' => 'Patient A',
                'height' => 170.0,
                'weight' => 70.0,
            ],
            [
                'user_id' => null,
                'fakeName' => 'Patient B',
                'height' => 165.0,
                'weight' => 60.0,
            ],
            [
                'user_id' => null,
                'fakeName' => 'Patient C',
                'height' => 180.0,
                'weight' => 80.0,
            ],
            [
                'user_id' => null,
                'fakeName' => 'Patient D',
                'height' => 155.0,
                'weight' => 55.0,
            ],
            [
                'user_id' => null,
                'fakeName' => 'Patient E',
                'height' => 175.0,
                'weight' => 75.0,
            ],
            [
                'user_id' => null,
                'fakeName' => 'Patient F',
                'height' => 160.0,
                'weight' => 65.0,
            ],
            [
                'user_id' => null,
                'fakeName' => 'Patient G',
                'height' => 185.0,
                'weight' => 85.0,
            ],
            [
                'user_id' => null,
                'fakeName' => 'Patient H',
                'height' => 150.0,
                'weight' => 50.0,
            ],
            [
                'user_id' => null,
                'fakeName' => 'Patient I',
                'height' => 170.0,
                'weight' => 70.0,
            ],
            [
                'user_id' => null,
                'fakeName' => 'Patient J',
                'height' => 165.0,
                'weight' => 60.0,
            ],
        ];

        foreach ($patients as $patientData) {
            // Create a user for each patient
            $user = User::create([
                'fullName' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('123123123'),
                'role' => 'Patient',
                'isVerified' => true,
                'phoneNumber' => fake()->phoneNumber(),
                'photoPath' => fake()->imageUrl(),
                'address' => fake()->city(),
                'birthday' => fake()->date(),
                'gender' => fake()->randomElement(['Male', 'Female']),
            ]);

            $patientData['user_id'] = $user->id;
            Patient::create($patientData);
        }
    }
}
