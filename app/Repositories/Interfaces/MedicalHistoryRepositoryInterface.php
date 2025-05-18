<?php

namespace App\Repositories\Interfaces;

use App\Models\MedicalHistory;
use App\Models\User;

interface MedicalHistoryRepositoryInterface
{
    public function updateOrCreateMedicalHistory(int $userId, array $data): array;
    
    public function findByUserId(int $userId): ?MedicalHistory;

    public function deleteMedicalHistory(int $userId): bool;

    public function existsForUser(int $userId): bool;
}
