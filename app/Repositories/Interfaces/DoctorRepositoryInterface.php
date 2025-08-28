<?php

namespace App\Repositories\Interfaces;

interface DoctorRepositoryInterface
{
    public function findByUserId($userId);
    public function create(array $data);
    public function update($id, array $data);
    public function updateProfileFull($userId, array $userData, array $doctorData, array $specialtyData = []);
    public function find($id);
    public function delete($id);
    public function getSpecialties($doctorId);
    public function addSpecialty($doctorId, array $data);
    public function updateSpecialty($doctorId, $specialtyId, array $data);
    public function deleteSpecialty($doctorId, $specialtyId);
    public function getByMedicalTag($medicalTagId);
}
