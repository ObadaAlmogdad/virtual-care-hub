<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ConsultationResultService;
use Illuminate\Support\Facades\Auth;

class ConsultationResultController extends Controller
{
    protected $consultationResultService;

    public function __construct(ConsultationResultService $consultationResultService)
    {
        $this->consultationResultService = $consultationResultService;
    }

    public function getMyDoctorReply($consultationId)
    {
        $patientId = Auth::user()->patient->id;
        // dd($consultationId);
        $reply = $this->consultationResultService->getReplyForSpecificConsultation($patientId, $consultationId);
        // dd($reply->toArray());
        if (!$reply) {
            return response()->json([
                'status' => false,
                'message' => 'لا يوجد رد لهذه الاستشارة أو أنها لا تخصك'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'consultation_id' => $reply->consultation_id,
                'doctor_name' => $reply->consultation->doctor->user->fullName,
                'reply' => $reply->replayOfDoctor,
                'accepted' => $reply->accepted
            ]
        ]);
    }
}
