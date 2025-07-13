<?php

namespace App\Repositories;

use App\Models\Doctor;
use App\Models\DoctorSpecialty;
use App\Repositories\Interfaces\DoctorRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DoctorRepository implements DoctorRepositoryInterface
{
    protected $model;
    protected $specialtyModel;

    public function __construct(Doctor $model, DoctorSpecialty $specialtyModel)
    {
        $this->model = $model;
        $this->specialtyModel = $specialtyModel;
    }

    public function findByUserId($userId)
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $doctor = $this->model->find($id);
        if ($doctor) {
            $doctor->update($data);
            return $doctor;
        }
        return null;
    }

    public function updateProfileFull($userId, array $userData, array $doctorData, array $specialtyData = [])
{
    $doctor = $this->findByUserId($userId);
    if (!$doctor) return null;

    $user = $doctor->user;
    $specialty = $doctor->specialties()->first();
    $user->update($userData);
    $doctor->update($doctorData);

    if ($specialty) {
        $specialty->update($specialtyData);
    }

    return $doctor->fresh()->load(['user', 'specialties']);
}


    public function find($id)
    {
        return $this->model->find($id);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function getSpecialties($doctorId)
    {
        return $this->specialtyModel->with('medicalTag')
            ->where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->get()
            ->map(function ($item) {
                return [
                    'doctor_id' => $item->doctor_id,
                    'medical_tag' => $item->medicalTag->name,
                    'start_time' => $item->start_time,
                    'end_time' => $item->end_time,
                    'photo' => $item->photo,
                    'consultation_fee' => $item->consultation_fee,
                    'is_active' => $item->is_active,
                ];
            });
    }

    public function addSpecialty($doctorId, array $data)
    {
        $data['doctor_id'] = $doctorId;
        return $this->specialtyModel->create($data);
    }

    public function updateSpecialty($doctorId, $specialtyId, array $data)
    {
        $specialty = $this->specialtyModel->where('doctor_id', $doctorId)
            ->where('id', $specialtyId)
            ->first();

        if ($specialty) {
            if (isset($data['photo']) && $specialty->photo) {
                Storage::disk('public')->delete($specialty->photo);
            }
            $specialty->update($data);
            return $specialty;
        }
        return null;
    }

    public function deleteSpecialty($doctorId, $specialtyId)
    {
        $specialty = $this->specialtyModel->where('doctor_id', $doctorId)
            ->where('id', $specialtyId)
            ->first();

        if ($specialty) {
            if ($specialty->photo) {
                Storage::disk('public')->delete($specialty->photo);
            }
            return $specialty->delete();
        }
        return false;
    }
}
