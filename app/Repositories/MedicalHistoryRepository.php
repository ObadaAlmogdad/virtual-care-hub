<?php

namespace App\Repositories;

use App\Models\MedicalHistory;
use App\Repositories\Interfaces\MedicalHistoryRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MedicalHistoryRepository implements MedicalHistoryRepositoryInterface
{
    public function updateOrCreateMedicalHistory(int $userId, array $data): array
    {
        $medicalHistory = MedicalHistory::updateOrCreate(
            ['user_id' => $userId],
            $this->prepareData($data)
        );

        return $this->formatResponse($medicalHistory);
    }

    private function prepareData(array $data): array
    {
        return [
            'general_diseases' => isset($data['general_diseases']) ? json_encode($data['general_diseases']) : null,
            'chronic_diseases' => json_encode($data['chronic_diseases']),
            'surgeries' => $data['surgeries'] ?? null,
            'allergies' => $data['allergies'],
            'permanent_medications' => $data['permanent_medications'] ?? null,
            'medical_documents_path' => $data['medical_documents_path'] ?? null
        ];
    }

    private function formatResponse(MedicalHistory $medicalHistory): array
    {
        return [
            'id' => $medicalHistory->id,
            'user_id' => $medicalHistory->user_id,
            'last_updated' => $medicalHistory->updated_at->toIso8601String(),

            // الحقول من نوع JSON
            'general_diseases' => json_decode($medicalHistory->general_diseases) ?? [],
            'chronic_diseases' => json_decode($medicalHistory->chronic_diseases) ?? [],

            // الحقول النصية
            'surgeries' => $medicalHistory->surgeries ?? null,
            'allergies' => $medicalHistory->allergies,
            'permanent_medications' => $medicalHistory->permanent_medications ?? null,

            // حقل المسار
            'medical_documents_path' => $medicalHistory->medical_documents_path ?? null,

            // إضافة مفيدة للواجهات الأمامية
            'document_url' => $medicalHistory->medical_documents_path
                ? asset($medicalHistory->medical_documents_path)
                : null
        ];
    }

    public function deleteMedicalHistory(int $userId): bool
    {
        $medicalHistory = $this->findByUserId($userId);

        if ($medicalHistory) {
            return $medicalHistory->delete();
        }

        return false;
    }

    public function existsForUser(int $userId): bool
    {
        return MedicalHistory::where('user_id', $userId)->exists();
    }

    public function findByUserId(int $userId): ?MedicalHistory
    {
        return MedicalHistory::where('user_id', $userId)->first();
    }
}
