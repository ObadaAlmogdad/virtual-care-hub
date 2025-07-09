<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Services\AppointmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    // ⬅️ Return available days for booking
    public function availableDays(Doctor $doctor)
    {
        $days = $this->appointmentService->getAvailableDays($doctor);
        return response()->json($days);
    }

    // ⬅️ Return time slots for selected date
    public function availableSlots(Request $request, Doctor $doctor)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $slots = $this->appointmentService->getAvailableSlots($doctor, $request->date);
        return response()->json($slots);
    }

    // ⬅️ Book appointment
    public function book(Request $request, Doctor $doctor)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'user_note' => 'nullable|string',
        ]);

        $user = Auth::user();
        $slots = $this->appointmentService->getAvailableSlots($doctor, $request->date);

        if (!in_array($request->time, $slots)) {
            throw ValidationException::withMessages([
                'time' => 'هذا الوقت غير متاح.'
            ]);
        }

        $appointment = $this->appointmentService->bookAppointment([
            'patient_id' => $user->id,
            'doctor_id' => $doctor->id,
            'date' => $request->date,
            'day' => strtolower(Carbon::parse($request->date)->format('l')),
            'time' => $request->time,
            'user_note' => $request->user_note,
        ]);

        return response()->json([
            'message' => 'تم حجز الموعد بنجاح.',
            'details' => [
                'day' => $appointment->day,
                'date' => $appointment->date,
                'time' => $appointment->time,
            ]
        ]);
    }
}
