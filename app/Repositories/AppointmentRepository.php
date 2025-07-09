<?php

namespace App\Repositories;

use App\Models\Appointment;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;

class AppointmentRepository implements AppointmentRepositoryInterface
{
    public function getAppointmentsByDoctorAndDate(int $doctorId, string $date)
    {
        return Appointment::where('doctor_id', $doctorId)
            ->whereDate('date', $date)
            ->get();
    }

    public function create(array $data)
    {
        return Appointment::create($data);
    }
}
