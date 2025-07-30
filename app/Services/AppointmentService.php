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
        $workDays      = $doctor->work_days;
        $today         = Carbon::today('Asia/Damascus');
        for ($i = 0; $i < $daysAhead; $i++) {
            $date    = $today->copy()->addDays($i);
            $dayName = strtolower($date->format('l'));
            // dd($dayName);

            if (in_array($dayName, $workDays)) {
                $availableSlots = $this->getAvailableSlots($doctor, $date->toDateString());

                if (! empty($availableSlots)) {
                    $availableDays[] = [
                        'date' => $date->toDateString(),
                        'day'  => $dayName,
                    ];
                }
            }
        }

        return $availableDays;
    }

    public function getAvailableSlots(Doctor $doctor, string $date): array
    {
        $workDays = $doctor->work_days;
        $dayName  = strtolower(Carbon::parse($date,'Asia/Damascus')->format('l'));

        if (! in_array($dayName, $workDays)) {
            return []; // doctor doesn't work on this day
        }

        $start        = Carbon::parse($doctor->work_time_in,'Asia/Damascus');
        $end          = Carbon::parse($doctor->work_time_out,'Asia/Damascus');
        $slotDuration = $doctor->time_for_waiting?? 20;

        $bookedTimes = $this->appointmentRepo
            ->getAppointmentsByDoctorAndDate($doctor->id, $date)
            ->pluck('time')
            ->map(fn($t) => Carbon::parse($t,'Asia/Damascus')->format('H:i'))
            ->toArray();

        $slots = [];
        while ($start->lt($end)) {
            $time = $start->format('H:i');
            if (! in_array($time, $bookedTimes)) {
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
