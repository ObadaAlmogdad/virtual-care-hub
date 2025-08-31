<?php

namespace App\Repositories\Interfaces;

interface ConsultationResultRepositoryInterface
{
    public function getDoctorReplyForSpecificConsultation($consultationId);
}
