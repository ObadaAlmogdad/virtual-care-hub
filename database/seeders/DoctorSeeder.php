<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\User;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample doctors
        $doctors = [
            [
                'user_id' => null, // Will be set after user creation
                'bio' => 'Experienced internal medicine specialist with over 10 years of practice.',
                'activatePoint' => '100',
                'rating' => 4.8,
                'work_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'work_time_in' => '09:00:00',
                'work_time_out' => '17:00:00',
                'time_for_waiting' => 20,
            ],
            [
                'user_id' => null,
                'bio' => 'Pediatrician specializing in child health and development.',
                'activatePoint' => '95',
                'rating' => 4.9,
                'work_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                'work_time_in' => '08:00:00',
                'work_time_out' => '16:00:00',
                'time_for_waiting' => 25,
            ],
            [
                'user_id' => null,
                'bio' => 'Dermatologist with expertise in skin conditions and treatments.',
                'activatePoint' => '90',
                'rating' => 4.7,
                'work_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                'work_time_in' => '10:00:00',
                'work_time_out' => '18:00:00',
                'time_for_waiting' => 30,
            ],
            [
                'user_id' => null,
                'bio' => 'Orthopedic surgeon specializing in bone and joint health.',
                'activatePoint' => '85',
                'rating' => 4.6,
                'work_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday'],
                'work_time_in' => '08:00:00',
                'work_time_out' => '16:00:00',
                'time_for_waiting' => 45,
            ],
            [
                'user_id' => null,
                'bio' => 'Psychiatrist with focus on mental health and therapy.',
                'activatePoint' => '80',
                'rating' => 4.5,
                'work_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'work_time_in' => '09:00:00',
                'work_time_out' => '17:00:00',
                'time_for_waiting' => 60,
            ],
        ];

        foreach ($doctors as $doctorData) {
            // Create a user for each doctor
            $user = User::create([
                'fullName' => 'Dr. ' . fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'role' => 'Doctor',
                'isVerified' => true,
                'phoneNumber' => fake()->phoneNumber(),
                'address' => fake()->city(),
                'birthday' => fake()->date(),
                'gender' => fake()->randomElement(['Male', 'Female']),
            ]);

            $doctorData['user_id'] = $user->id;
            Doctor::create($doctorData);
        }
    }
}
