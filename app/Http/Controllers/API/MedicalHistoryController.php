<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalHistoryRequest;
use App\Services\MedicalHistoryService;
use Illuminate\Http\JsonResponse;

class MedicalHistoryController extends Controller
{

    protected $medicalHistoryService;

    public function __construct(MedicalHistoryService $medicalHistoryService)
    {
        $this->medicalHistoryService = $medicalHistoryService;
    }


    public function store(MedicalHistoryRequest $request): JsonResponse
    {
        try {
            $medicalHistory = $this->medicalHistoryService->handleMedicalHistory(data: $request->validated());

            return response()->json([
                'data' => $medicalHistory,
                'message' => 'medicalHistory saved'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ],404);
        }
    }
}
