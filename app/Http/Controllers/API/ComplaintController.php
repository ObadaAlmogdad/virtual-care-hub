<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Complaint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    // List complaints
    public function index()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            $complaints = Complaint::all();
        } else {
            $complaints = Complaint::where('user_id', $user->id)->get();
        }
        return response()->json(['data' => $complaints]);
    }

    // Store a new complaint
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consultation_id' => 'sometimes|exists:consultations,id',
            'header' => 'required|string|max:255',
            'content' => 'required|string',
            'media' => 'nullable|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $data['user_id'] = auth()->id();
        $complaint = Complaint::create($data);
        return response()->json(['data' => $complaint], 201);
    }

    // Show a complaint (admin or owner only)
    public function show($id)
    {
        $user = auth()->user();
        $complaint = Complaint::findOrFail($id);
        if (!$user->isAdmin() && $complaint->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json(['data' => $complaint]);
    }

    // Update a complaint (admin or owner only, partial update allowed)
    public function update(Request $request, $id)
    {
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
        $user = auth()->user();
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $count = \App\Models\Complaint::count();
        return response()->json(['count' => $count]);
    }
}
