<?php

namespace App\Services;

use App\Models\Doctor;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use Carbon\Carbon;

class AppointmentService
{
    protected $appointmentRepo;

    public function __construct(AppointmentRepositoryInterface $appointmentRepo)
    {
        $this->appointmentRepo = $appointmentRepo;
    }

    public function getAvailableDays(Doctor $doctor, int $daysAhead = 14): array
    {
        // dd($doctor->toArray());
        $availableDays = [];
        $workDays      = $doctor->work_days;
        $today         = Carbon::today('Asia/Damascus');
        for ($i = 0; $i < $daysAhead; $i++) {
            $date    = $today->copy()->addDays($i);
            $dayName = $date->format('l');

            if (in_array($dayName, $workDays)) {
                $availableSlots = $this->getAvailableSlots($doctor, $date->toDateString());
                // dd($availableSlots);

                if (!empty($availableSlots)) {
                    $availableDays[] = [
                        'date' => $date->toDateString(),
                        'day'  => $dayName,
                    ];
                    // dd($availableDays);
                }
            }
        }

        return $availableDays;
    }

    public function getAvailableSlots(Doctor $doctor, string $date): array
    {
        $workDays = $doctor->work_days;
        $dayName  = Carbon::parse($date, 'Asia/Damascus')->format('l');

        if (!in_array($dayName, $workDays)) {
            return []; // doctor doesn't work on this day
        }

        $start        = Carbon::parse($doctor->work_time_in, 'Asia/Damascus');
        $end          = Carbon::parse($doctor->work_time_out, 'Asia/Damascus');
        $slotDuration = $doctor->time_for_waiting ?? 20;

        $bookedTimes = $this->appointmentRepo
            ->getAppointmentsByDoctorAndDate($doctor->id, $date)
            ->pluck('time')
            ->map(fn ($t) => Carbon::parse($t, 'Asia/Damascus')->format('H:i'))
            ->toArray();

        $slots = [];
        while ($start->lt($end)) {
            $time = $start->format('H:i');
            if (!in_array($time, $bookedTimes)) {
                $slots[] = $time;
            }
            $start->addMinutes($slotDuration);
        }

        return $slots;
    }

    public function bookAppointment(array $data)
    {
        return $this->appointmentRepo->create($data);
    }

    public function getAppointmentsByPatient($patientId)
    {
        return $this->appointmentRepo->getAppointmentsByPatient($patientId);
    }

    public function filterAppointmentsByTime($patientId, $type)
    {
        return $this->appointmentRepo->filterByTime($patientId, $type);
    }

    public function getDoctorAppointments(int $doctorId, ?string $filter = null)
    {
        return $this->appointmentRepo->getDoctorAppointments($doctorId, $filter);
    }

}
