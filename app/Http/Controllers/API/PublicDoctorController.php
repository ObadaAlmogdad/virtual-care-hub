<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\MedicalTag;
use Illuminate\Http\Request;

class PublicDoctorController extends Controller
{
    /**
     * Get all doctors (without authentication)
     */
    public function index()
    {
        try {
            \Log::info('Starting doctors fetch');

            $doctors = Doctor::with(['user', 'specialties.medicalTag'])
                ->whereHas('user', function ($query) {
                    $query->where('isVerified', true);
                })
                ->get();

            \Log::info('Doctors fetched successfully', ['count' => $doctors->count()]);

            return response()->json([
                'status' => 'success',
                'data' => $doctors
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching doctors', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching doctors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor profile by ID (without authentication)
     */
    public function show($id)
    {
        try {
            $doctor = Doctor::with(['user', 'specialties.medicalTag'])
                ->where('id', $id)
                ->first();

            if (!$doctor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Doctor not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $doctor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching doctor information'
            ], 500);
        }
    }

    /**
     * Get doctors by specialty ID (without authentication)
     */
    public function getBySpecialty($specialtyId)
    {
        try {
            \Log::info('Starting doctors by specialty fetch', ['specialtyId' => $specialtyId]);

            $doctors = Doctor::with(['user', 'specialties.medicalTag'])
                ->whereHas('specialties', function ($query) use ($specialtyId) {
                    $query->where('medical_tag_id', $specialtyId);
                    //->where('is_active', true);

                })
                // ->whereHas('user', function ($query) {
                //     $query->where('isVerified', true);
                // })
                ->get();

            \Log::info('Doctors by specialty fetched successfully', [
                'specialtyId' => $specialtyId,
                'count' => $doctors->count()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $doctors
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching doctors by specialty', [
                'specialtyId' => $specialtyId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching doctors by specialty: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search doctors by name or specialty (without authentication)
     */
    public function search(Request $request)
    {
        try {
            $query = Doctor::with(['user', 'specialties.medicalTag'])
                ->whereHas('user', function ($query) {
                    $query->where('isVerified', true);
                });

            if ($request->has('name')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('fullName', 'like', '%' . $request->name . '%');
                });
            }

            if ($request->has('specialty')) {
                $query->whereHas('specialties.medicalTag', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->specialty . '%')
                        ->orWhere('name_ar', 'like', '%' . $request->specialty . '%');
                });
            }

            $doctors = $query->get();

            return response()->json([
                'status' => 'success',
                'data' => $doctors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while searching doctors'
            ], 500);
        }
    }
}
