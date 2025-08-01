<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * إنشاء محادثة جديدة (للطبيب فقط)
     */
    public function createChat(Request $request)
    {
        $user = Auth::user();

        // التحقق من أن المستخدم طبيب
        if (!$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'فقط الأطباء يمكنهم إنشاء محادثات'
            ], 403);
        }

        $request->validate([
            'patient_id' => 'required|exists:users,id',
        ]);

        // التحقق من أن المريض موجود
        $patient = User::find($request->patient_id);
        if (!$patient->isPatient()) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم المحدد ليس مريض'
            ], 400);
        }

        // التحقق من وجود محادثة سابقة
        $existingChat = Chat::where('doctor_id', $user->doctor->id)
            ->where('patient_id', $patient->patient->id)
            ->first();

        if ($existingChat) {
            return response()->json([
                'success' => true,
                'message' => 'المحادثة موجودة مسبقاً',
                'chat' => $existingChat->load('doctor.user', 'patient.user', 'messages.sender')
            ]);
        }

        // إنشاء محادثة جديدة
        $chat = Chat::create([
            'doctor_id' => $user->doctor->id,
            'patient_id' => $patient->patient->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المحادثة بنجاح',
            'chat' => $chat->load('doctor.user', 'patient.user')
        ], 201);
    }

    /**
     * الحصول على محادثات المستخدم (للطبيب والمريض)
     */
    public function myChats(Request $request)
    {
        $user = Auth::user();

        $chats = Chat::with(['doctor.user', 'patient.user', 'messages' => function ($query) {
            $query->latest()->limit(1); // آخر رسالة فقط
        }])
            ->where(function ($query) use ($user) {
                if ($user->isDoctor()) {
                    $query->where('doctor_id', $user->doctor->id);
                } elseif ($user->isPatient()) {
                    $query->where('patient_id', $user->patient->id);
                }
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'chats' => $chats
        ]);
    }

    /**
     * الحصول على محادثة محددة
     */
    public function getChat($chat_id)
    {
        $user = Auth::user();

        $chat = Chat::with(['doctor.user', 'patient.user'])
            ->where('id', $chat_id)
            ->where(function ($query) use ($user) {
                if ($user->isDoctor()) {
                    $query->where('doctor_id', $user->doctor->id);
                } elseif ($user->isPatient()) {
                    $query->where('patient_id', $user->patient->id);
                }
            })
            ->first();

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'المحادثة غير موجودة'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'chat' => $chat
        ]);
    }




    public function getmyChatId()
    {
        $user = Auth::user();

        if ($user->isDoctor()) {
            $chats = \App\Models\Chat::where('doctor_id', $user->doctor->id)->pluck('id');
        } elseif ($user->isPatient()) {
            $chats = \App\Models\Chat::where('patient_id', $user->patient->id)->pluck('id');
        } else {
            $chats = collect();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرجاع محادثات المستخدم بنجاح',
            'chats' => $chats
        ]);
    }
}
