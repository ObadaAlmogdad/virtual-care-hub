<?php

namespace App\Repositories\Interfaces;

interface AppointmentRepositoryInterface
{
    public function getAppointmentsByDoctorAndDate(int $doctorId, string $date);
    public function create(array $data);

}
