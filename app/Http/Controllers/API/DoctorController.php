<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorSpecialty;
use App\Services\DoctorService;

class DoctorController extends Controller
{
    protected $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    public function updateProfile(Request $request)
    {
        try {
            $doctor = $this->doctorService->updateProfile(auth()->id(), $request->all());
            return response()->json([
                'message' => 'Profile updated successfully',
                'doctor' => $doctor->load('licenses')
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function uploadLicense(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $file = $request->file('license');
        $path = $file->store('licenses', 'public');

        $fileRecord = File::create([
            'path' => $path,
            'origanName' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension()
        ]);

        $doctor->files()->attach($fileRecord->id, ['type' => 'license']);

        return response()->json([
            'message' => 'License uploaded successfully',
            'file' => $fileRecord
        ]);
    }

    public function getProfile()
    {
        try {
            $doctor = $this->doctorService->getProfile(auth()->id());
            return response()->json(['doctor' => $doctor]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function deleteLicense($fileId)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $file = File::find($fileId);

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        // Check if the file belongs to the doctor
        $pivot = $doctor->files()->where('file_id', $fileId)->first();

        if (!$pivot) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete the file from storage
        Storage::disk('public')->delete($file->path);

        // Delete the file record and its pivot
        $file->delete();

        return response()->json(['message' => 'License deleted successfully']);
    }

    public function getSpecialties()
    {
        try {
            $specialties = $this->doctorService->getSpecialties(auth()->id());
            return response()->json([
                'status' => 'success',
                'data' => $specialties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function getDoctorSpecialties($doctor_id)
    {
        try {
            $specialties = $this->doctorService->getDoctorSpecialties($doctor_id);
            return response()->json([
                'status' => 'success',
                'data' => $specialties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function addSpecialty(Request $request)
    {
        try {
            $specialty = $this->doctorService->addSpecialty(auth()->id(), $request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Specialty added successfully',
                'data' => $specialty
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function updateSpecialty(Request $request, $specialtyId)
    {
        try {
            $specialty = $this->doctorService->updateSpecialty(auth()->id(), $specialtyId, $request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Specialty updated successfully',
                'data' => $specialty
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function deleteSpecialty($specialtyId)
    {
        try {
            $this->doctorService->deleteSpecialty(auth()->id(), $specialtyId);
            return response()->json([
                'status' => 'success',
                'message' => 'Specialty deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
