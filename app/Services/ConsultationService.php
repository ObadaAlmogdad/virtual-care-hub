<?php

namespace App\Services;

use App\Repositories\Interfaces\ConsultationRepositoryInterface;
use App\Models\DoctorSpecialty;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ConsultationService
{
    protected $consultationRepository;

    public function __construct(ConsultationRepositoryInterface $consultationRepository)
    {
        $this->consultationRepository = $consultationRepository;
    }

    public function createConsultation(array $data)
    {
        $validator = Validator::make($data, [
            'doctor_id' => 'nullable|exists:doctors,id',
            'medical_tag_id' => 'required|exists:medical_tags,id',
            'isSpecial' => 'required|boolean',
            'problem' => 'required|string',
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
            'isAnonymous' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Get the fee from doctor's specialty
        if (isset($data['doctor_id'])) {
            $specialty = DoctorSpecialty::where('doctor_id', $data['doctor_id'])
                ->where('medical_tag_id', $data['medical_tag_id'])
                ->where('is_active', true)
                ->first();

            if (!$specialty) {
                throw new ValidationException(Validator::make([], [
                    'doctor_id' => 'Doctor does not have this specialty'
                ]));
            }

            $data['fee'] = $specialty->consultation_fee;
        } else {
            // For general consultations, set a default fee
            $data['fee'] = 0;
        }

        // Handle media files
        if (isset($data['media']) && is_array($data['media'])) {
            $mediaPaths = [];
            foreach ($data['media'] as $file) {
                if ($file->isValid()) {
                    $path = $file->store('consultations', 'public');
                    $mediaPaths[] = $path;
                }
            }
            $data['media'] = implode(',', $mediaPaths);
        } else {
            $data['media'] = '';
        }

        return $this->consultationRepository->create($data);
    }

    public function getPendingConsultations($doctorId)
    {
        return $this->consultationRepository->getPendingConsultations($doctorId);
    }

    public function getDoctorConsultationsByStatus($doctorId, $status = null)
    {
        if ($status) {
            $validator = Validator::make(['status' => $status], [
                'status' => 'required|in:pending,accepted,rejected,scheduled,completed'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }

        return $this->consultationRepository->getDoctorConsultationsByStatus($doctorId, $status);
    }

    public function getUserConsultationsByStatus($userId, $status = null)
    {
        if ($status) {
            $validator = Validator::make(['status' => $status], [
                'status' => 'required|in:pending,accepted,rejected,scheduled,completed'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }

        return $this->consultationRepository->getUserConsultationsByStatus($userId, $status);
    }

    public function getUserConsultations($userId)
    {
        return $this->consultationRepository->getUserConsultations($userId);
    }

    public function updateConsultationStatus($id, $status)
    {
        $validator = Validator::make(['status' => $status], [
            'status' => 'required|in:accepted,rejected'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->consultationRepository->updateStatus($id, $status);
    }

    public function scheduleConsultation($id, $scheduledAt, $reminderBeforeMinutes)
    {
        $validator = Validator::make([
            'scheduled_at' => $scheduledAt,
            'reminder_before_minutes' => $reminderBeforeMinutes
        ], [
            'scheduled_at' => 'required|date|after:now',
            'reminder_before_minutes' => 'required|integer|min:5|max:1440'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->consultationRepository->scheduleConsultation($id, $scheduledAt, $reminderBeforeMinutes);
    }
} 