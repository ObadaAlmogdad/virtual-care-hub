<?php

namespace App\Services;

use App\Models\Doctor;
use App\Repositories\AppointmentRepository;
use Carbon\Carbon;

class AppointmentService
{
    protected $appointmentRepo;

    public function __construct(AppointmentRepository $appointmentRepo)
    {
        $this->appointmentRepo = $appointmentRepo;
    }

    public function getAvailableDays(Doctor $doctor, int $daysAhead = 14): array
    {
        $availableDays = [];
        $workDays = json_decode($doctor->work_days, true);

        $today = Carbon::today();
        for ($i = 0; $i < $daysAhead; $i++) {
            $date = $today->copy()->addDays($i);
            $dayName = strtolower($date->format('l'));

            if (in_array($dayName, $workDays)) {
                $availableDays[] = [
                    'date' => $date->toDateString(),
                    'day' => $dayName
                ];
            }
        }

        return $availableDays;
    }

    public function getAvailableSlots(Doctor $doctor, string $date): array
    {
        $workDays = json_decode($doctor->work_days, true);
        $dayName = strtolower(Carbon::parse($date)->format('l'));

        if (!in_array($dayName, $workDays)) {
            return []; // doctor doesn't work on this day
        }

        $start = Carbon::parse($doctor->work_time_in);
        $end = Carbon::parse($doctor->work_time_out);
        $slotDuration = 20;

        $bookedTimes = $this->appointmentRepo
            ->getAppointmentsByDoctorAndDate($doctor->id, $date)
            ->pluck('time')
            ->map(fn($t) => Carbon::parse($t)->format('H:i'))
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
}
