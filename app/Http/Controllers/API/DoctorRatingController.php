<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating; 
use App\Models\User;

class DoctorRatingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $patientId = auth()->id(); // لو تستخدم Sanctum مثلاً

        // تعديل التقييم لو موجود أو إنشاء جديد
        $rating = Rating::updateOrCreate(
            ['doctor_id' => $validated['doctor_id'], 'patient_id' => $patientId],
            ['rating' => $validated['rating'], 'comment' => $validated['comment']]
        );

        return response()->json($rating);
    }

    public function getDoctorRatings($doctorId)
    {
        $ratings = Rating::where('doctor_id', $doctorId)->latest()->get();

        $average = Rating::where('doctor_id', $doctorId)->avg('rating');

        return response()->json([
            'average_rating' => round($average, 2),
            'ratings' => $ratings,
        ]);
    }

    public function topRatedDoctors()
    {
        $topDoctors = User::whereHas('doctorRatings')
            ->withAvg('doctorRatings as average_rating', 'rating')
            ->orderByDesc('average_rating')
            ->take(10)
            ->get();

        return response()->json($topDoctors);
    }
}
