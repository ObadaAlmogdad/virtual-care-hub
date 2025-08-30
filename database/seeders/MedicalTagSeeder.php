<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicalTag;

class MedicalTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicalTags = [
            [
                'name' => 'Internal Medicine',
                'name_ar' => 'باطنية',
                'description' => 'Internal medicine and general health',
                'icon' => 'stethoscope',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'Psychiatry',
                'name_ar' => 'نفسية',
                'description' => 'Mental health and psychology',
                'icon' => 'brain',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'Pediatrics',
                'name_ar' => 'أطفال',
                'description' => 'Child and adolescent health',
                'icon' => 'baby',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'name' => 'Orthopedics',
                'name_ar' => 'عظام',
                'description' => 'Bone and joint health',
                'icon' => 'bone',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'name' => 'Dermatology',
                'name_ar' => 'جلدية',
                'description' => 'Skin health and conditions',
                'icon' => 'skin',
                'is_active' => true,
                'order' => 5,
            ],
            [
                'name' => 'Cardiology',
                'name_ar' => 'قلب',
                'description' => 'Heart and cardiovascular health',
                'icon' => 'heart',
                'is_active' => true,
                'order' => 6,
            ],
            [
                'name' => 'Neurology',
                'name_ar' => 'أعصاب',
                'description' => 'Nervous system and brain health',
                'icon' => 'brain',
                'is_active' => true,
                'order' => 7,
            ],
            [
                'name' => 'Ophthalmology',
                'name_ar' => 'عيون',
                'description' => 'Eye health and vision',
                'icon' => 'eye',
                'is_active' => true,
                'order' => 8,
            ],
        ];

        foreach ($medicalTags as $tag) {
            MedicalTag::create($tag);
        }
    }
}
