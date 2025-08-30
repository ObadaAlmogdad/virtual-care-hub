<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic Plan',
                'price' => 29.99,
                'duration' => 30,
                'is_active' => true,
                'priority' => 1,
                'expected_wait_minutes' => 30,
                'private_consultations_quota' => 2,
                'ai_consultations_quota' => 10,
                'max_family_members' => 2,
                'savings_percent' => 0,
            ],
            [
                'name' => 'Premium Plan',
                'price' => 59.99,
                'duration' => 30,
                'is_active' => true,
                'priority' => 2,
                'expected_wait_minutes' => 15,
                'private_consultations_quota' => 5,
                'ai_consultations_quota' => 25,
                'max_family_members' => 4,
                'savings_percent' => 10,
            ],
            [
                'name' => 'Family Plan',
                'price' => 99.99,
                'duration' => 30,
                'is_active' => true,
                'priority' => 3,
                'expected_wait_minutes' => 10,
                'private_consultations_quota' => 10,
                'ai_consultations_quota' => 50,
                'max_family_members' => 6,
                'savings_percent' => 20,
            ],
            [
                'name' => 'Enterprise Plan',
                'price' => 199.99,
                'duration' => 30,
                'is_active' => true,
                'priority' => 4,
                'expected_wait_minutes' => 5,
                'private_consultations_quota' => 20,
                'ai_consultations_quota' => 100,
                'max_family_members' => 10,
                'savings_percent' => 25,
            ],
            [
                'name' => 'Trial Plan',
                'price' => 9.99,
                'duration' => 7,
                'is_active' => true,
                'priority' => 0,
                'expected_wait_minutes' => 45,
                'private_consultations_quota' => 1,
                'ai_consultations_quota' => 5,
                'max_family_members' => 1,
                'savings_percent' => 0,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::create($planData);
        }
    }
}
