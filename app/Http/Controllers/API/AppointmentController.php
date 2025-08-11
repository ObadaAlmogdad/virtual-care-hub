<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function availableDays(Doctor $doctor)
    {

        $days = $this->appointmentService->getAvailableDays($doctor);
        return response()->json($days);
    }

    public function availableSlots(Request $request, Doctor $doctor)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $slots = $this->appointmentService->getAvailableSlots($doctor, $request->date);
        return response()->json($slots);
    }

    public function book(Request $request, Doctor $doctor)
    {
        $request->validate([
            'date'      => 'required|date',
            'time'      => 'required|date_format:H:i',
            'user_note' => 'nullable|string',
        ]);

        $user = Auth::user();

        $alreadyBooked = \App\Models\Appointment::where('patient_id', $user->patient->id)
            ->where('doctor_id', $doctor->id)
            ->exists();

        if ($alreadyBooked) {
            throw ValidationException::withMessages([
                'appointment' => 'لقد قمت بحجز موعد سابق مع هذا الطبيب.',
            ]);
        }
        $slots = $this->appointmentService->getAvailableSlots($doctor, $request->date);

        if (!in_array($request->time, $slots)) {
            throw ValidationException::withMessages([
                'time' => 'هذا الوقت غير متاح.',
            ]);
        }

        $appointment = $this->appointmentService->bookAppointment([
            'patient_id' => $user->patient->id,
            'doctor_id'  => $doctor->id,
            'date'       => $request->date,
            'day'        => strtolower(Carbon::parse($request->date, 'Asia/Damascus')->format('l')),
            'time'       => $request->time,
            'user_note'  => $request->user_note,
        ]);

        return response()->json([
            'message' => 'تم حجز الموعد بنجاح.',
            'details' => [
                'day'  => $appointment->day,
                'date' => $appointment->date,
                'time' => $appointment->time,
            ],
        ]);
    }

    public function getPatientAppointments()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if (!$user->patient) {
            return response()->json(['error' => 'Patient data not found'], 404);
        }
        $patientId    = Auth::user()->patient->id;
        $appointments = $this->appointmentService->getAppointmentsByPatient($patientId);

        return response()->json([
            'status' => 'success',
            'data'   => $appointments,
        ]);
    }

    public function filterPatientAppointments(Request $request)
    {
        $patientId = auth()->user()->patient->id;
        $status = $request->query('type'); // 'past' or 'upcoming'

        if (!in_array($status, ['past', 'upcoming'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid filter type. Use "past" or "upcoming".'
            ], 422);
        }

        $appointments = $this->appointmentService->filterAppointmentsByTime($patientId, $status);

        return response()->json([
            'status' => 'success',
            'data' => $appointments
        ]);
    }
}
