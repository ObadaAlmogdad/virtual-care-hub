<?php

namespace App\Services;

use App\Repositories\Interfaces\DoctorRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DoctorService
{
    protected $doctorRepository;

    public function __construct(DoctorRepositoryInterface $doctorRepository)
    {
        $this->doctorRepository = $doctorRepository;
    }

public function updateProfile($userId, array $data)
{
    $doctor = $this->doctorRepository->findByUserId($userId);
    if (!$doctor) throw new \Exception('Doctor not found', 404);

    $user = $doctor->user;
    $specialty = $doctor->specialties()->first();

    // Debug: Log the incoming data
    Log::info('Doctor profile update data:', $data);

    // Build user data - only include fields that are present in the request
    $userData = [];
    if (array_key_exists('fullName', $data)) {
        $userData['fullName'] = $data['fullName'];
    }
    if (array_key_exists('phoneNumber', $data)) {
        $userData['phoneNumber'] = $data['phoneNumber'];
    }
    if (array_key_exists('address', $data)) {
        $userData['address'] = $data['address'];
    }
    if (array_key_exists('birthday', $data)) {
        $userData['birthday'] = $data['birthday'];
    }
    if (array_key_exists('gender', $data)) {
        $userData['gender'] = $data['gender'];
    }
    if (array_key_exists('photoPath', $data)) {
        $userData['photoPath'] = $data['photoPath'];
    }

    // Build doctor data - only include fields that are present in the request
    $doctorData = [];
    if (array_key_exists('bio', $data)) {
        $doctorData['bio'] = $data['bio'];
    }
    if (array_key_exists('activatePoint', $data)) {
        $doctorData['activatePoint'] = $data['activatePoint'];
    }
    if (array_key_exists('facebook_url', $data)) {
        $doctorData['facebook_url'] = $data['facebook_url'];
    }
    if (array_key_exists('instagram_url', $data)) {
        $doctorData['instagram_url'] = $data['instagram_url'];
    }
    if (array_key_exists('twitter_url', $data)) {
        $doctorData['twitter_url'] = $data['twitter_url'];
    }
    if (array_key_exists('address', $data)) {
        $doctorData['address'] = $data['address'];
    }
    if (array_key_exists('work_days', $data)) {
        $doctorData['work_days'] = json_encode($data['work_days']);
    }
    if (array_key_exists('work_time_in', $data)) {
        $doctorData['work_time_in'] = $data['work_time_in'];
    }
    if (array_key_exists('work_time_out', $data)) {
        $doctorData['work_time_out'] = $data['work_time_out'];
    }
    if (array_key_exists('time_for_waiting', $data)) {
        $doctorData['time_for_waiting'] = (int)$data['time_for_waiting'];
    }

    // Build specialty data - only include fields that are present in the request
    $specialtyData = [];
    if ($specialty) {
        if (array_key_exists('start_time', $data)) {
            $specialtyData['start_time'] = $data['start_time'];
        }
        if (array_key_exists('end_time', $data)) {
            $specialtyData['end_time'] = $data['end_time'];
        }
        if (array_key_exists('consultation_fee', $data)) {
            $specialtyData['consultation_fee'] = (float)$data['consultation_fee'];
        }
        if (array_key_exists('description', $data)) {
            $specialtyData['description'] = $data['description'];
        }
        if (array_key_exists('yearOfExper', $data)) {
            $specialtyData['yearOfExper'] = (int)$data['yearOfExper'];
        }
    }

    // Debug: Log the processed data
    Log::info('Processed user data:', $userData);
    Log::info('Processed doctor data:', $doctorData);
    Log::info('Processed specialty data:', $specialtyData);

    return $this->doctorRepository->updateProfileFull($userId, $userData, $doctorData, $specialtyData);
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
}
