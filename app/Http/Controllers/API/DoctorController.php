<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
}
