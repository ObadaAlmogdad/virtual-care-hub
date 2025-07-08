<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Consultation;
use App\Models\ConsultationAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ConsultationService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ConsultationController extends Controller
{
    protected $consultationService;

    public function __construct(ConsultationService $consultationService)
    {
        $this->consultationService = $consultationService;
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $data['user_id'] = auth()->id();

            // Handle media files
            if ($request->hasFile('media')) {
                $data['media'] = $request->file('media');
            }

            $consultation = $this->consultationService->createConsultation($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Consultation created successfully',
                'data' => $consultation
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the consultation',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
