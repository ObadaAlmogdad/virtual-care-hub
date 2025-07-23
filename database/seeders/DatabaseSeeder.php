<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ComplaintSeeder::class,
            UserQuestionTagAnswerSeeder::class,
            AppointmentSeeder::class,
            ConsultationSeeder::class,
            ConsultationAnswerSeeder::class,
            ConsultationResultSeeder::class,
            ConsultationResultHistorySeeder::class,
            DoctorSeeder::class,
            DoctorSpecialtySeeder::class,
            FileSeeder::class,
            MedicalHistorySeeder::class,
            MedicalTagSeeder::class,
            PatientSeeder::class,
            PlanSeeder::class,
            QuestionSeeder::class,
            RatingSeeder::class,
            SubscriptionSeeder::class,
            TransactionSeeder::class,
            WalletSeeder::class,
            PaymentSeeder::class,
            QuestionMedicalTagSeeder::class,
            DoctorSpecialtiesSeeder::class,
        ]);
    }
}
