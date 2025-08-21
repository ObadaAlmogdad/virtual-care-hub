<?php

namespace App\Repositories;

use App\Models\ConsultationResult;
use App\Repositories\Interfaces\ConsultationResultRepositoryInterface;

class ConsultationResultRepository implements ConsultationResultRepositoryInterface
{
    protected $model;

    public function __construct(ConsultationResult $model)
    {
        $this->model = $model;
    }

    public function getDoctorReplyForSpecificConsultation($patientId, $consultationId)
    {
        // dd("asdkashdk");
        return $this->model
            ->whereHas('consultation', function ($q) use ($patientId, $consultationId) {
                $q->where('patient_id', $patientId)
                  ->where('id', $consultationId);
            })
            ->with(['consultation', 'consultation.doctor.user'])
            ->first();
    }
}
