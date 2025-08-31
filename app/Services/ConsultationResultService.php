<?php

namespace App\Services;

use App\Repositories\Interfaces\ConsultationResultRepositoryInterface;

class ConsultationResultService
{
    protected $consultationResultRepository;

    public function __construct(ConsultationResultRepositoryInterface $consultationResultRepository)
    {
        $this->consultationResultRepository = $consultationResultRepository;
    }

    public function getReplyForSpecificConsultation($consultationId)
    {
        return $this->consultationResultRepository->getDoctorReplyForSpecificConsultation( $consultationId);
    }
}
