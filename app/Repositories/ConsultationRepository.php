<?php

namespace App\Repositories;

use App\Models\Consultation;
use App\Models\ConsultationResult;
use App\Repositories\Interfaces\ConsultationRepositoryInterface;

class ConsultationRepository implements ConsultationRepositoryInterface
{
    protected $model;

    public function __construct(Consultation $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Consultation
    {
        return $this->model->create($data);
    }

    public function findById($id): ?Consultation
    {
        return $this->model->find($id);
    }

    public function update($id, array $data): bool
    {
        $consultation = $this->model->find($id);
        if ($consultation) {
            return $consultation->update($data);
        }
        return false;
    }

    public function getPendingConsultations($doctorId)
    {
        return $this->model->where('doctor_id', $doctorId)
            ->where('status', 'pending')
            ->with(['user', 'medicalTag'])
            ->get();
    }

    public function getDoctorConsultationsByStatus($doctorId, $status = null)
    {
        $query = $this->model->where('doctor_id', $doctorId)
            ->with(['user', 'medicalTag']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function getUserConsultationsByStatus($userId, $status = null)
    {
        $query = $this->model->where('patient_id', $userId)
            ->with(['doctor', 'medicalTag']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function getUserConsultations($userId)
    {
        return $this->model->where('patient_id', $userId)
            ->with(['doctor', 'medicalTag'])
            ->get();
    }

    public function scheduleConsultation($id, $scheduledAt, $reminderBeforeMinutes): bool
    {
        return $this->update($id, [
            'scheduled_at' => $scheduledAt,
            'reminder_before_minutes' => $reminderBeforeMinutes,
            'status' => 'scheduled'
        ]);
    }

    public function updateStatus($id, $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

public function storeDoctorReply(array $data)
{
    $consultation = $this->model->findOrFail($data['consultation_id']);

    // if ($consultation->doctor_id !== auth()->id()) {
    //     throw new \Exception("Unauthorized access.");
    // }

    return ConsultationResult::create([
        'consultation_id' => $data['consultation_id'],
        'user_question_tag_answer_id' => $data['user_question_tag_answer_id'],
        'replayOfDoctor' => $data['replayOfDoctor'],
        'accepted' => $data['accepted'],
    ]);
}

public function getGeneralConsultations()
{
    return $this->model
        ->where('isSpecial', 0)
        ->with(['user', 'doctor', 'medicalTag'])
        ->orderByDesc('created_at')
        ->get();
}

public function getwebGeneralConsultations()
{
    return $this->model
        ->where('isSpecial', 0)
        ->with(['user', 'doctor.user', 'medicalTag'])
        ->orderByDesc('created_at')
        ->paginate(10);
}


public function countGeneralConsultations(): int
    {
        return $this->model->where('isSpecial', 0)->count();
    }

public function countSpecialConsultations(): int
    {
        return $this->model->where('isSpecial', 1)->count();
    }

    public function countByMedicalTag()
{
    return $this->model
        ->selectRaw('medical_tag_id, COUNT(*) as consultations_count')
        ->groupBy('medical_tag_id')
        ->with('medicalTag')
        ->get();
}

public function getDoctorResponseRatesBySpecialty()
{
    return $this->model
        ->selectRaw('
            medical_tag_id,
            COUNT(consultations.id) as total_consultations,
            COUNT(DISTINCT consultation_results.consultation_id) as responded_consultations
        ')
        ->leftJoin('consultation_results', 'consultations.id', '=', 'consultation_results.consultation_id')
        ->groupBy('medical_tag_id')
        ->with('medicalTag')
        ->get()
        ->map(function ($item) {
            $responseRate = $item->total_consultations > 0
                ? round(($item->responded_consultations / $item->total_consultations) * 100, 2)
                : 0;

            return [
                'medical_tag' => $item->medicalTag ? $item->medicalTag->name : null,
                'total_consultations' => $item->total_consultations,
                'responded_consultations' => $item->responded_consultations,
                'response_rate' => $responseRate . '%',
            ];
        });
}



}
