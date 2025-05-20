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

    public function updateProfile($userId, array $data)
    {
        $validator = Validator::make($data, [
            'bio' => 'required|string',
            'yearOfExper' => 'required|string',
            'activatePoint' => 'string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $doctor = $this->doctorRepository->findByUserId($userId);
        if (!$doctor) {
            throw new \Exception('Doctor not found', 404);
        }

        return $this->doctorRepository->update($doctor->id, $data);
    }

    public function getProfile($userId)
    {
        $doctor = $this->doctorRepository->findByUserId($userId);
        if (!$doctor) {
            throw new \Exception('Doctor not found', 404);
        }

        return $doctor->load(['user', 'licenses']);
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