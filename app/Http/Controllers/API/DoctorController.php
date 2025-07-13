<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorSpecialty;
use App\Services\DoctorService;
use App\Services\ConsultationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DoctorController extends Controller
{
    protected $doctorService;
    protected $consultationService;

    public function __construct(DoctorService $doctorService, ConsultationService $consultationService)
    {
        $this->doctorService = $doctorService;
        $this->consultationService = $consultationService;
    }

    public function updateProfile(Request $request)
    {
        try {
            // Debug: Log the request data
            Log::info('Request data in controller:', $request->all());

            $doctor = $this->doctorService->updateProfile(auth()->id(), $request->all());
            return response()->json([
                'message' => 'Profile updated successfully',
                'doctor' => $doctor
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function getProfile()
    {
        try {
            $doctor = $this->doctorService->getProfile(auth()->id());
            return response()->json(['doctor' => $doctor]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function getSpecialties()
    {
        try {
            $specialties = $this->doctorService->getSpecialties(auth()->id());
            return response()->json([
                'status' => 'success',
                'data' => $specialties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function getDoctorSpecialties($doctor_id)
    {
        try {
            $specialties = $this->doctorService->getDoctorSpecialties($doctor_id);
            return response()->json([
                'status' => 'success',
                'data' => $specialties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function addSpecialty(Request $request)
    {
        try {
            $specialty = $this->doctorService->addSpecialty(auth()->id(), $request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Specialty added successfully',
                'data' => $specialty
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function updateSpecialty(Request $request, $specialtyId)
    {
        try {
            $specialty = $this->doctorService->updateSpecialty(auth()->id(), $specialtyId, $request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Specialty updated successfully',
                'data' => $specialty
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function deleteSpecialty($specialtyId)
    {
        try {
            $this->doctorService->deleteSpecialty(auth()->id(), $specialtyId);
            return response()->json([
                'status' => 'success',
                'message' => 'Specialty deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function getPendingConsultations()
    {
        try {
            $consultations = $this->consultationService->getPendingConsultations(auth()->id());
            return response()->json([
                'status' => 'success',
                'data' => $consultations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching pending consultations'
            ], 500);
        }
    }

    public function getConsultationsByStatus(Request $request)
    {
        try {
            $status = $request->query('status');
            $consultations = $this->consultationService->getDoctorConsultationsByStatus(auth()->id(), $status);
            return response()->json([
                'status' => 'success',
                'data' => $consultations
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching consultations'
            ], 500);
        }
    }

    public function updateConsultationStatus(Request $request, $consultationId)
    {
        try {
            $status = $request->input('status');
            $result = $this->consultationService->updateConsultationStatus($consultationId, $status);

            return response()->json([
                'status' => 'success',
                'message' => 'Consultation status updated successfully',
                'data' => $result
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating consultation status'
            ], 500);
        }
    }

    public function scheduleConsultation(Request $request, $consultationId)
    {
        try {
            $scheduledAt = $request->input('scheduled_at');
            $reminderBeforeMinutes = $request->input('reminder_before_minutes', 30);

            $result = $this->consultationService->scheduleConsultation(
                $consultationId,
                $scheduledAt,
                $reminderBeforeMinutes
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Consultation scheduled successfully',
                'data' => $result
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }  catch (\Exception $e) {
            Log::error('Schedule consultation error', [
                'id' => $consultationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while scheduling the consultation'
            ], 500);
        }
    }
}
