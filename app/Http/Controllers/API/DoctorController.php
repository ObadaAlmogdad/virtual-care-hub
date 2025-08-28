<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        } catch (\Exception $e) {
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

public function replyToAnswer(Request $request, $consultationId, $answerId=1)
{
    $request->validate([
        'replayOfDoctor' => 'required|string',
        // 'accepted' => 'required|boolean',
    ]);

    try {
        $data = [
            'consultation_id' => $consultationId,
            'user_question_tag_answer_id' => $answerId,
            'replayOfDoctor' => $request->input('replayOfDoctor'),
            'accepted' =>true,
            // 'accepted' => $request->input('accepted'),
        ];


        $result = $this->consultationService->storeDoctorReply($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Doctor reply saved',
            'data' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to save reply',
            'error' => $e->getMessage()
        ], 500);
    }
}



    public function getBySpecialty($medicalTagId)
    {
        return $this->doctorService->getDoctorsBySpecialty($medicalTagId);
    }

    public function updateProfile(Request $request)
    {
        // dd($request->toArray());

        $validator = Validator::make($request->all(), [
            'fullName' => 'sometimes|string|max:255',
            'phoneNumber' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
            'birthday' => 'sometimes|date',
            'gender' => 'sometimes|in:man,woman',
            'photoPath' => 'sometimes|file|image|max:2048',

            'bio' => 'sometimes|string|max:1000',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'doctor_address' => 'nullable|string|max:255',
            'work_days' => 'sometimes|array',
            'work_days.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'work_time_in' => 'sometimes|date_format:H:i:s',
            'work_time_out' => 'sometimes|date_format:H:i:s',
            'time_for_waiting' => 'sometimes|integer|min:0',

            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after_or_equal:start_time',
            'consultation_fee' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'yearOfExper' => 'sometimes|string|max:50',
            'photo' => 'sometimes|file|image|max:2048',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        try {
            $doctor = $this->doctorService->updateProfile(auth()->id(), $request->all());
            return response()->json([
                'message' => 'Profile updated successfully',
                'doctor' => $doctor
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
