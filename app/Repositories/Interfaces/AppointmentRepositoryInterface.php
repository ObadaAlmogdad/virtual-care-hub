<?php

namespace App\Repositories\Interfaces;

interface AppointmentRepositoryInterface
{
    public function getAppointmentsByDoctorAndDate(int $doctorId, string $date);
    public function create(array $data);
    public function getAppointmentsByPatient($patientId);
    public function filterByTime($patientId, $type);
    public function getDoctorAppointments(int $doctorId, ?string $filter = null);
}
