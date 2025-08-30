<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\DoctorSpecialty;
use App\Models\MedicalTag;

class DoctorSpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::all();
        $medicalTags = MedicalTag::all();

        if ($doctors->isEmpty() || $medicalTags->isEmpty()) {
            return;
        }

        foreach ($doctors as $doctor) {
            // Each doctor gets 1-2 specialties
            $specialtyCount = rand(1, 2);
            $selectedTags = $medicalTags->random($specialtyCount);

            foreach ($selectedTags as $tag) {
                DoctorSpecialty::create([
                    'doctor_id' => $doctor->id,
                    'medical_tag_id' => $tag->id,
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'consultation_fee' => $this->getRandomConsultationFee(),
                    'is_active' => true,
                    'yearOfExper' => rand(1, 20),
                    'description' => 'Specialist in ' . $tag->name,
                ]);
            }
        }
    }

    private function getRandomConsultationFee(): float
    {
        $fees = [25.00, 30.00, 35.00, 40.00, 45.00, 50.00, 55.00, 60.00, 75.00, 80.00, 100.00];
        return $fees[array_rand($fees)];
    }
}
