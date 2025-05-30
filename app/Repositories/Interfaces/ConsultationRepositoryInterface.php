<?php

namespace App\Repositories\Interfaces;

use App\Models\Consultation;

interface ConsultationRepositoryInterface
{
    public function create(array $data): Consultation;
    public function findById($id): ?Consultation;
    public function update($id, array $data): bool;
    public function getPendingConsultations($doctorId);
    public function getUserConsultations($userId);
    public function scheduleConsultation($id, $scheduledAt, $reminderBeforeMinutes): bool;
    public function updateStatus($id, $status): bool;
} 