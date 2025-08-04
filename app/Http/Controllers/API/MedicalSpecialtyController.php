<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalTag;
use App\Models\Doctor;
use Illuminate\Http\Request;

class MedicalSpecialtyController extends Controller
{
    public function index()
    {
        try {
            $specialties = MedicalTag::where('is_active', true)
                ->orderBy('order', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $specialties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching medical specialties'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $specialty = MedicalTag::with(['doctorSpecialties.doctor.user'])
                ->where('is_active', true)
                ->find($id);

            if (!$specialty) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Medical specialty not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $specialty
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching medical specialty'
            ], 500);
        }
    }

    public function getDoctorsBySpecialty($specialtyId)
    {
        try {
            // \Log::info('Starting medical specialty doctors fetch', ['specialtyId' => $specialtyId]);

            $doctors = Doctor::with(['user', 'specialties.medicalTag'])
                ->whereHas('specialties', function ($query) use ($specialtyId) {
                    $query->where('medical_tag_id', $specialtyId);
                })
                ->whereHas('user', function ($query) {
                    $query->where('isVerified', true);
                })
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $doctors
            ]);
        } catch (\Exception $e) {
            // \Log::error('Error fetching medical specialty doctors', [
            //     'specialtyId' => $specialtyId,
            //     'message' => $e->getMessage(),
            //     'file' => $e->getFile(),
            //     'line' => $e->getLine()
            // ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching doctors by specialty: ' . $e->getMessage()
            ], 500);
        }
    }
}
