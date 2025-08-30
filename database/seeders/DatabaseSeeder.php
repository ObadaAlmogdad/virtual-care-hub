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
            
            // Additional seeders for complete system
            FileSeeder::class,                    // Create sample files
            MedicalHistorySeeder::class,          // Create medical histories
            QuestionSeeder::class,                // Create medical questions
            QuestionMedicalTagSeeder::class,      // Link questions to tags
            UserQuestionTagAnswerSeeder::class,   // Create user answers
            ConsultationSeeder::class,            // Create consultations
            ConsultationResultSeeder::class,      // Create consultation results
            ConsultationResultHistorySeeder::class, // Create result histories
            ConsultationAnswerSeeder::class,      // Create consultation answers
            RatingSeeder::class,                  // Create doctor ratings
            ComplaintSeeder::class,               // Create complaints
            AppointmentSeeder::class,             // Create appointments
            PaymentSeeder::class,                 // Create payments
        ]);
    }
}
