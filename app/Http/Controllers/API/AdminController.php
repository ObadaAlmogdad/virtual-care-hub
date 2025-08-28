<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdminService;
use App\Models\MedicalTag;
use App\Models\User;


class AdminController extends Controller
{

    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function verficat($id)
    {
        try {
            $this->adminService->verficatAccount($id);

            return response()->json([
                'status' => 1,
                'message' => 'account verificating successfully',
            ], 200);
        } catch (\Exception $err) {
            return response()->json([
                'status' => 0,
                'message' => $err->getMessage(),
            ], 400);
        }
    }

    /**
     * Get all medical tags
     */
    public function getMedicalTags()
    {
        $tags = MedicalTag::orderBy('order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $tags
        ]);
    }

    /**
     * Add a new medical tag
     */
    public function addMedicalTag(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:medical_tags',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'required|image|max:2048',
            'is_active' => 'boolean',
            'order' => 'integer|min:0'
        ]);

        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('medical_tage', 'public');
            $validated['icon'] = $path;  // رابط الصورة قابل للوصول
        }

        $tag = MedicalTag::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Medical tag added successfully',
            'data' => $tag
        ], 201);
    }

    /**
     * Update a medical tag
     */
    public function updateMedicalTag(Request $request, $id)
    {
        $tag = MedicalTag::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:medical_tags,name,' . $id,
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'order' => 'integer|min:0'
        ]);

        $tag->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Medical tag updated successfully',
            'data' => $tag
        ]);
    }

    // Delete a medical tag
    public function deleteMedicalTag($id)
    {
        $tag = MedicalTag::findOrFail($id);

        // Check if tag is being used by any doctor
        if ($tag->doctorSpecialties()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete medical tag as it is being used by doctors'
            ], 422);
        }

        $tag->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Medical tag deleted successfully'
        ]);
    }

    public function countUsersByRole()
    {
        try {
            $counts = User::selectRaw('role, count(*) as count')
                ->groupBy('role')
                ->get()
                ->pluck('count', 'role'); // تحويل النتيجة لـ key-value pairs

            return response()->json([
                'success' => true,
                'data' => [
                    'doctor' => $counts->get('Doctor', 0),
                    'patient' => $counts->get('Patient', 0),
                    'admin' => $counts->get('Admin', 0)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user counts'
            ], 500);
        }
    }

    /**
     * Get all doctors with details for admin
     */
    public function getAllDoctors(Request $request)
    {
        $doctors = \App\Models\Doctor::with(['user', 'specialties.medicalTag'])
            ->paginate(20);

        $result = $doctors->map(function ($doctor) {
            $user = $doctor->user;
            $specialty = $doctor->specialties->first();
            $medicalTag = $specialty && $specialty->medicalTag ? $specialty->medicalTag->name : null;
            return [
                'id' => $user ? $user->id : null,
                'doctor_name' => $user ? $user->fullName : null,
                'email' => $user ? $user->email : null,
                'specialty' => $medicalTag,
                'city' => $user ? $user->address : null,
                'rating' => $doctor->rating ?? 0,
                'status' => $user && $user->isVerified ? 'مفعل' : 'غير مفعل',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $result,
            'current_page' => $doctors->currentPage(),
            'last_page' => $doctors->lastPage(),
            'per_page' => $doctors->perPage(),
            'total' => $doctors->total(),
        ]);
    }

    /**
     * Get all patients with useful details for admin
     */
    public function getAllPatients(Request $request)
    {
        $patients = \App\Models\Patient::with('user')
            ->paginate(20);

        $result = $patients->map(function ($patient) {
            $user = $patient->user;
            return [
                'id' => $user ? $user->id : null,
                'name' => $user ? $user->fullName : null,
                'email' => $user ? $user->email : null,
                'address' => $user ? $user->address : null,
                'gender' => $user ? $user->gender : null,
                'birthday' => $user ? $user->birthday : null,
                'height' => $patient->height,
                'weight' => $patient->weight,
                'status' => $user && $user->isVerified ? 'مفعل' : 'غير مفعل',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $result,
            'current_page' => $patients->currentPage(),
            'last_page' => $patients->lastPage(),
            'per_page' => $patients->perPage(),
            'total' => $patients->total(),
        ]);
    }
}
