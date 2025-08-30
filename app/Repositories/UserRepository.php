<?php

namespace App\Repositories;

use App\Models\MedicalHistory;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Models\Patient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function findById($id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function createUser(array $data): User
    {
        return $this->model->create($data);
    }

    public function updateProfile($userId, array $userData, ?array $patientData = null, ?array $medicalData = null)
{
        $user=$this->findById($userId);
        if (!$user)
            return null;

        $user->update($userData);
        $patient=null;
        $medical=null;
        if ($patientData && $user->patient) {
            $patient=$user?->patient;
            $patient->update($patientData);
        }
        if ($medicalData) {
            $medical=$patient?->medicalHistory;
            if ($medical) {
                $medical->update($medicalData);
            } elseif ($user->patient) {
                $medicalData['patient_id'] = $user->patient->id;
                MedicalHistory::create($medicalData);
            }
        }
    return $patient->load('user', 'medicalHistory');
}

    public function getAllAdmin()
    {
        return $this->model->where('role','Admin')->paginate(10);
    }

    public function findAdminById(int $id)
    {
        return $this->model->where('role', 'Admin')->findOrFail($id);
    }

}
