<?php

namespace App\Services;

use App\Repositories\Interfaces\DoctorRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DoctorService
{
    protected $doctorRepository;

    public function __construct(DoctorRepositoryInterface $doctorRepository)
    {
        $this->doctorRepository = $doctorRepository;
    }


    public function addSpecialtyBySystem($doctorId, array $data)
    {
        return $this->doctorRepository->addSpecialty($doctorId, $data);
    }


    public function create(array $data)
    {
        return $this->doctorRepository->create($data);
    }

    public function getProfile($userId)
    {
        $doctor = $this->doctorRepository->findByUserId($userId);
        if (!$doctor) {
            throw new \Exception('Doctor not found', 404);
        }

        return $doctor->load(['user', 'specialties']);
    }

    public function getSpecialties($userId)
    {
        $doctor = $this->doctorRepository->findByUserId($userId);
        if (!$doctor) {
            throw new \Exception('Doctor not found', 404);
        }

        return $this->doctorRepository->getSpecialties($doctor->id);
    }

    public function getDoctorSpecialties($userId)
    {
        $doctor = $this->doctorRepository->findByUserId($userId);
        if (!$doctor) {
            throw new \Exception('Doctor not found', 404);
        }

        return $this->doctorRepository->getSpecialties($doctor->id);
    }

    public function addSpecialty($userId, array $data)
    {
        $validator = Validator::make($data, [
            'medical_tag_id' => 'required|exists:medical_tags,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'photo' => 'nullable|image|max:2048',
            'consultation_fee' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $doctor = $this->doctorRepository->findByUserId($userId);
        if (!$doctor) {
            throw new \Exception('Doctor not found', 404);
        }

        if (isset($data['photo'])) {
            $data['photo'] = $data['photo']->store('specialties', 'public');
        }

        return $this->doctorRepository->addSpecialty($doctor->id, $data);
    }

    public function updateSpecialty($userId, $specialtyId, array $data)
    {
        $validator = Validator::make($data, [
            'medical_tag_id' => 'sometimes|exists:medical_tags,id',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'photo' => 'nullable|image|max:2048',
            'consultation_fee' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $doctor = $this->doctorRepository->findByUserId($userId);
        if (!$doctor) {
            throw new \Exception('Doctor not found', 404);
        }

        if (isset($data['photo'])) {
            $data['photo'] = $data['photo']->store('specialties', 'public');
        }

        return $this->doctorRepository->updateSpecialty($doctor->id, $specialtyId, $data);
    }

    public function deleteSpecialty($userId, $specialtyId)
    {
        $doctor = $this->doctorRepository->findByUserId($userId);
        if (!$doctor) {
            throw new \Exception('Doctor not found', 404);
        }

        return $this->doctorRepository->deleteSpecialty($doctor->id, $specialtyId);
    }
    public function getDoctorsBySpecialty($medicalTagId)
    {
        $doctors = $this->doctorRepository->getByMedicalTag($medicalTagId);
        return response()->json($doctors);
    }

    public function updateProfile($userId, array $data)
    {
        $doctor = $this->doctorRepository->findByUserId($userId);
        if (!$doctor) throw new \Exception('Doctor not found', 404);

        $user = $doctor->user;
        $specialty = $doctor->specialties()->first();

        // معالجة رفع صورة الملف الشخصي للطبيب (photoPath)
        if (isset($data['photoPath']) && $data['photoPath'] instanceof \Illuminate\Http\UploadedFile) {
            // تخزين الصورة في مجلد profiles داخل التخزين العام
            $storedPath = $data['photoPath']->store('profiles', 'public');
            $data['photoPath'] = $storedPath;
        }
        $userData = [
            'fullName' => $data['fullName'] ?? $user->fullName,
            'phoneNumber' => $data['phoneNumber'] ?? $user->phoneNumber,
            'address' => $data['address'] ?? $user->address,
            'birthday' => $data['birthday'] ?? $user->birthday,
            'gender' => $data['gender'] ?? $user->gender,
            'photoPath' => $data['photoPath'] ?? $user->photoPath,
        ];

        $doctorData = [
            'bio' => $data['bio'] ?? $doctor->bio,
            'facebook_url' => $data['facebook_url'] ?? $doctor->facebook_url,
            'instagram_url' => $data['instagram_url'] ?? $doctor->instagram_url,
            'twitter_url' => $data['twitter_url'] ?? $doctor->twitter_url,
            'address' => $data['doctor_address'] ?? $doctor->address,
            'work_days' => isset($data['work_days']) ? $data['work_days'] : $doctor->work_days,
            'work_time_in' => $data['work_time_in'] ?? $doctor->work_time_in,
            'work_time_out' => $data['work_time_out'] ?? $doctor->work_time_out,
            'time_for_waiting' => $data['time_for_waiting'] ?? $doctor->time_for_waiting,
        ];

        $specialtyData = [];
        if ($specialty) {
            if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
                $specialtyData['photo'] = $data['photo']->store('specialties', 'public');
            }

            $specialtyData = [
                'start_time' => $data['start_time'] ?? $specialty->start_time,
                'end_time' => $data['end_time'] ?? $specialty->end_time,
                'consultation_fee' => $data['consultation_fee'] ?? $specialty->consultation_fee,
                'description' => $data['description'] ?? $specialty->description,
                'yearOfExper' => $data['yearOfExper'] ?? $specialty->yearOfExper,
            ];
        }

        return $this->doctorRepository->updateProfileFull($userId, $userData, $doctorData, $specialtyData);
    }

    public function getUnverifiedDoctors()
    {
        return $this->doctorRepository->getUnverifiedDoctors();
    }



public function rejectDoctor(int $doctorId, string $reason)
{
    $doctor = $this->doctorRepository->rejectDoctorVerification($doctorId, $reason);

    return $doctor;
}

}
