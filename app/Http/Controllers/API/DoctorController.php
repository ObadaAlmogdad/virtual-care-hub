<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorSpecialty;

class DoctorController extends Controller
{
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bio' => 'required|string',
            'yearOfExper' => 'required|string',
            'activatePoint' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $doctor->update($request->only(['bio', 'yearOfExper', 'activatePoint']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'doctor' => $doctor->load('licenses')
        ]);
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
        $doctor = Doctor::where('user_id', auth()->id())
            ->with(['user', 'licenses'])
            ->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        return response()->json(['doctor' => $doctor]);
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
        $user = auth()->user();

        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Doctor not found'
            ], 404);
        }

        $specialties = DoctorSpecialty::with('medicalTag')
            ->where('doctor_id', $doctor->id)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $specialties
        ]);
    }

    public function getDoctorSpecialties($doctor_id)
    {
        $doctor = Doctor::where('user_id', $doctor_id)->first();

        if (!$doctor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Doctor not found'
            ], 404);
        }

        $specialties = DoctorSpecialty::with('medicalTag')
            ->where('doctor_id', $doctor->id)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $specialties
        ]);
    }

    public function addSpecialty(Request $request)
    {
        $userId = auth()->user()->id;

        $doctor = Doctor::where('user_id', $userId)->first();

        if (!$doctor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Doctor not found'
            ], 404);
        }

        $validatedData = $request->validate([
            'medical_tag_id' => 'required|exists:medical_tags,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'photo' => 'nullable|image|max:2048',
            'consultation_fee' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000'
        ]);

        // دمج doctor_id مع البيانات الصحيحة
        $validatedData['doctor_id'] = $doctor->id; // يُفضل استخدام doctor_id من الجدول doctors بدلاً من user_id

        if ($request->hasFile('photo')) {
            $validatedData['photo'] = $request->file('photo')->store('specialties', 'public');
        }

        // إنشاء التخصص مع جميع البيانات المدمجة
        $specialty = DoctorSpecialty::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Specialty added successfully',
            'data' => $specialty
        ], 201);
    }

    public function updateSpecialty(Request $request, $doctorId, $specialtyId)
    {
        $specialty = DoctorSpecialty::where('doctor_id', $doctorId)
            ->where('id', $specialtyId)
            ->firstOrFail();

        $request->validate([
            'medical_tag_id' => 'sometimes|exists:medical_tags,id',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'photo' => 'nullable|image|max:2048',
            'consultation_fee' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean'
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($specialty->photo) {
                Storage::disk('public')->delete($specialty->photo);
            }
            $data['photo'] = $request->file('photo')->store('specialties', 'public');
        }

        $specialty->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Specialty updated successfully',
            'data' => $specialty
        ]);
    }

    public function deleteSpecialty($doctorId, $specialtyId)
    {
        $specialty = DoctorSpecialty::where('doctor_id', $doctorId)
            ->where('id', $specialtyId)
            ->firstOrFail();

        if ($specialty->photo) {
            Storage::disk('public')->delete($specialty->photo);
        }

        $specialty->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Specialty deleted successfully'
        ]);
    }
}
