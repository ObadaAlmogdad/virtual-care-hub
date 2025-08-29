<?php

namespace App\Services;

use App\Models\Doctor;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use App\Services\BillingService;
use Carbon\Carbon;

class AppointmentService
{
    protected $appointmentRepo;
    protected $billingService;

    public function __construct(AppointmentRepositoryInterface $appointmentRepo, BillingService $billingService)
    {
        $this->appointmentRepo = $appointmentRepo;
        $this->billingService = $billingService;
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
        // Create appointment first
        $appointment = $this->appointmentRepo->create($data);

        // Determine fee: prefer active specialty fee; fallback to any specialty
        $doctor = \App\Models\Doctor::findOrFail($appointment->doctor_id);
        $specialty = $doctor->specialties()->where('is_active', true)->orderByDesc('consultation_fee')->first();
        if (!$specialty) {
            $specialty = $doctor->specialties()->orderByDesc('consultation_fee')->first();
        }
        $amount = (float) ($specialty?->consultation_fee ?? 0);

        if ($amount > 0) {
            // Fetch user ids
            $patientUserId = \App\Models\Patient::find($appointment->patient_id)->user_id;
            $doctorUserId = $doctor->user_id;

            // Process payment via subscription or wallet
            $this->billingService->processAppointmentPayment(
                $patientUserId,
                $doctorUserId,
                $amount,
                $appointment->id,
                \App\Models\Appointment::class
            );
        }

        return $appointment;
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

    public function getAllDoctorAppointments()
    {
        return $this->appointmentRepo->getAllDoctorAppointments();
    }
}
