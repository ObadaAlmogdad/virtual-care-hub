<?php

namespace App\Repositories;

use App\Models\Appointment;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use Carbon\Carbon;

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

    public function getAppointmentsByPatient($patientId)
    {
        return Appointment::where('patient_id', $patientId)
            ->with(['doctor.user'])
            ->orderByDesc('date')
            ->get();
    }



    public function filterByTime($patientId, $type)
    {
        $now = Carbon::now('Asia/Damascus');

        return Appointment::where('patient_id', $patientId)
            ->where(function ($query) use ($type, $now) {
                if ($type === 'past') {
                    $query->where(function ($q) use ($now) {
                        $q->where('date', '<', $now->toDateString())
                            ->orWhere(function ($q2) use ($now) {
                                $q2->where('date', '=', $now->toDateString())
                                    ->where('time', '<', $now->format('H:i'));
                            });
                    });
                } elseif ($type === 'upcoming') {
                    $query->where(function ($q) use ($now) {
                        $q->where('date', '>', $now->toDateString())
                            ->orWhere(function ($q2) use ($now) {
                                $q2->where('date', '=', $now->toDateString())
                                    ->where('time', '>=', $now->format('H:i'));
                            });
                    });
                }
            })
            ->with(['doctor.user'])
            ->orderBy('date')
            ->orderBy('time')
            ->get();
    }
}
