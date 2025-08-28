<?php

namespace App\Repositories;

use App\Models\Appointment;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use Carbon\Carbon;

class AppointmentRepository implements AppointmentRepositoryInterface
{
    protected $model;

    public function __construct(Appointment $model)
    {
        $this->model = $model;
    }

    public function getAppointmentsByDoctorAndDate(int $doctorId, string $date)
    {
        return $this->model
            ->where('doctor_id', $doctorId)
            ->whereDate('date', $date)
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function getAppointmentsByPatient($patientId)
    {
        return $this->model
            ->where('patient_id', $patientId)
            ->with(['doctor.user'])
            ->orderByDesc('date')
            ->get();
    }

    public function filterByTime($patientId, $type)
    {
        $now = Carbon::now('Asia/Damascus');

        return $this->model
            ->where('patient_id', $patientId)
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

    public function getDoctorAppointments(int $doctorId, ?string $filter = null)
    {
        $query = $this->model->where('doctor_id', $doctorId)->with('patient.user');

        if ($filter === 'past') {
            $query->where(function ($q) {
                $q->where('date', '<', Carbon::today('Asia/Damascus'))
                    ->orWhere(function ($q2) {
                        $q2->where('date', '=', Carbon::today('Asia/Damascus'))
                            ->where('time', '<', Carbon::now('Asia/Damascus')->format('H:i'));
                    });
            });
        } elseif ($filter === 'upcoming') {
            $query->where(function ($q) {
                $q->where('date', '>', Carbon::today('Asia/Damascus'))
                    ->orWhere(function ($q2) {
                        $q2->where('date', '=', Carbon::today('Asia/Damascus'))
                            ->where('time', '>=', Carbon::now('Asia/Damascus')->format('H:i'));
                    });
            });
        }

        return $query->orderBy('date')->orderBy('time')->get();
    }
}
