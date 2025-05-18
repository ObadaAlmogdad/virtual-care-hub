<?php

namespace App\Services;

use App\Repositories\Interfaces\MedicalHistoryRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MedicalHistoryService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MedicalHistoryRepositoryInterface $medicalHistoryRepository
    ) {
    }


    public function handleMedicalHistory(array $data): array
    {
        $userId = auth()->user()->id;

        return DB::transaction(function () use ($userId, $data) {
            $this->validateUserExists($userId);
            $this->validateMedicalData($data);

            return $this->medicalHistoryRepository->updateOrCreateMedicalHistory(
                userId: $userId,
                data: $data
            );
        });
    }


    private function validateUserExists(int $userId): void
    {
        if (!$this->userRepository->findById($userId)) {
            throw new ModelNotFoundException('المستخدم غير موجود', 404);
        }
    }

    private function validateMedicalData(array $data): void
    {
        Validator::make($data, [
            'chronic_diseases' => 'nullable|array|max:5',
            'chronic_diseases.*' => 'string|max:100',
            'allergies' => 'nullable|string|max:255',

        
            'general_diseases' => 'nullable|array',
            'general_diseases.*' => 'string|max:80',
            'surgeries' => 'nullable|string|max:500',
            'permanent_medications' => 'nullable|string|max:500',
            'medical_documents_path' => 'nullable|url|max:2048'
        ])->validate();
    }
}
