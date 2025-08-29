<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintCreatedNotification;
use App\Services\ComplaintService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class ComplaintController extends Controller
{

    protected $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    // List complaints
    public function index()
    {
        /**  @var User $user*/
        $user = auth()->user();
        if ($user->isAdmin()) {
            $complaints = Complaint::all();
        } else {
            $complaints = Complaint::where('user_id', $user->id)->get();
        }
        // تجهيز الرسبونس مع اسم الطبيب واسم المريض لكل شكوى
        $data = $complaints->map(function ($complaint) {
            // بيانات الطبيب
            $doctorName = null;
            $doctorEmail = null;
            $doctorId = null;
            $doctorRole = null;
            if ($complaint->consultation_id) {
                $consultation = $complaint->consultation;
                if ($consultation && $consultation->doctor) {
                    $doctorUser = $consultation->doctor->user;
                    if ($doctorUser) {
                        $doctorName = $doctorUser->fullName;
                        $doctorEmail = $doctorUser->email;
                        $doctorId = $doctorUser->id;
                        $doctorRole = $doctorUser->role;
                    }
                }
            }
            // بيانات المريض
            $patientUser = $complaint->user;
            $userData = $patientUser ? [
                'id' => $patientUser->id,
                'fullName' => $patientUser->fullName,
                'email' => $patientUser->email,
                'role' => $patientUser->role,
            ] : null;
            // دمج بيانات الشكوى مع بيانات المستخدم
            $complaintData = $complaint->toArray();
            $complaintData['user'] = $userData;
            return [
                'data' => $complaintData,
                'doctor_name' => $doctorName,
                'doctor_email' => $doctorEmail,
                'doctor_id' => $doctorId,
                'doctor_role' => $doctorRole,
            ];
        });
        return response()->json(['data' => $data]);
    }

    // Store a new complaint
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consultation_id' => 'sometimes|exists:consultations,id',
            'header' => 'required|string|max:255',
            'content' => 'required|string',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240', // دعم أنواع الصور والـ PDF حتى 10MB
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $data['user_id'] = auth()->id();

        // معالجة رفع الصور وتخزينها
        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('complaints_media', 'public');
                $mediaPaths[] = $path;
            }
        }
        $data['media'] = $mediaPaths;

        $complaint = Complaint::create($data);
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
        $admin->notify(new ComplaintCreatedNotification($complaint));
    }

        return response()->json(['data' => $complaint], 201);
    }

    // Show a complaint (admin or owner only)
    public function show($id)
    {
        /**  @var User $user*/
        $user = auth()->user();
        $complaint = Complaint::findOrFail($id);
        if (!$user->isAdmin() && $complaint->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $doctorName = null;
        $doctorEmail = null;
        $doctorId = null;
        $doctorRole = null;
        if ($complaint->consultation_id) {
            $consultation = $complaint->consultation;
            if ($consultation && $consultation->doctor) {
                $doctorUser = $consultation->doctor->user;
                if ($doctorUser) {
                    $doctorName = $doctorUser->fullName;
                    $doctorEmail = $doctorUser->email;
                    $doctorId = $doctorUser->id;
                    $doctorRole = $doctorUser->role;
                }
            }
        }
        // بيانات المريض
        $patientUser = $complaint->user;
        $userData = $patientUser ? [
            'id' => $patientUser->id,
            'fullName' => $patientUser->fullName,
            'email' => $patientUser->email,
            'role' => $patientUser->role,
        ] : null;
        // دمج بيانات الشكوى مع بيانات المستخدم
        $complaintData = $complaint->toArray();
        $complaintData['user'] = $userData;
        return response()->json([
            'data' => $complaintData,
            'doctor_name' => $doctorName,
            'doctor_email' => $doctorEmail,
            'doctor_id' => $doctorId,
            'doctor_role' => $doctorRole,
        ]);
    }

    // Update a complaint (admin or owner only, partial update allowed)
    public function update(Request $request, $id)
    {
        /**  @var User $user*/
        $user = auth()->user();
        $complaint = Complaint::findOrFail($id);
        if (!$user->isAdmin() && $complaint->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($user->isAdmin()) {
            // الأدمن يمكنه تحديث كل الحقول
            $validator = Validator::make($request->all(), [
                'consultation_id' => 'sometimes|exists:consultations,id',
                'header' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
                'type' => 'sometimes|string',
                'media' => 'sometimes|array',
                'answer' => 'sometimes|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $complaint->update($validator->validated());
        } else {
            // المالك لا يمكنه تعديل type أو answer
            $validator = Validator::make($request->all(), [
                'header' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
                'media' => 'nullable|array',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();
            // تجاهل أي محاولة لإرسال type أو answer
            $complaint->update($data);
        }
        return response()->json(['data' => $complaint]);
    }

    // Delete a complaint (admin or owner only)
    public function destroy($id)
    {
        /**  @var User $user*/
        $user = auth()->user();
        $complaint = Complaint::findOrFail($id);
        if (!$user->isAdmin() && $complaint->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $complaint->delete();
        return response()->json(['message' => 'Complaint deleted successfully']);
    }

    // Count complaints (admin only)
    public function count()
    {
        /**  @var User $user*/
        $user = auth()->user();
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $count = \App\Models\Complaint::count();
        return response()->json(['count' => $count]);
    }

    public function complaintsByType(Request $request)
    {
        $request->validate([
            'type' => 'required|in:patient,doctor',
        ]);

        $result = $this->complaintService->getComplaintsByType($request->type);

        return response()->json($result);
    }


}
