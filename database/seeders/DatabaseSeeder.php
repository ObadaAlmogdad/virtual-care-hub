<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run essential seeders in order
        $this->call([
            MedicalTagSeeder::class,      // Medical specialties
            DoctorSeeder::class,          // Create doctors
            PatientSeeder::class,         // Create patients
            DoctorSpecialtySeeder::class, // Assign specialties to doctors
            PlanSeeder::class,            // Create subscription plans
            WalletSeeder::class,          // Create wallets for users
            SubscriptionSeeder::class,    // Create subscriptions
            TransactionSeeder::class,     // Create financial transactions
        ]);
    }
}
