<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getAll();
    public function findById($id): ?User;
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): bool;
    public function delete(User $user): bool;
    public function createUser(array $data): User;
    public function updateProfile(User $user, array $userData, ?array $patientData = null, ?array $medicalData = null);

    public function getAllAdmin();
    public function findAdminById(int $id);

}
