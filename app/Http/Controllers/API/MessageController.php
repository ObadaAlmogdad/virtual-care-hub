<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\MessageEvent;
use App\Models\Message;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    /**
     * إرسال رسالة نصية أو ملف
     */
    public function sendMessage(Request $request, $chat_id)
    {
        $user = Auth::user();
        
        // التحقق من وجود المحادثة والصلاحية
        $chat = Chat::with(['doctor.user', 'patient.user'])
            ->where('id', $chat_id)
            ->where(function($query) use ($user) {
                if ($user->isDoctor() && $user->doctor) {
                    $query->where('doctor_id', $user->doctor->id);
                } elseif ($user->isPatient() && $user->patient) {
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

        $request->validate([
            'message_content' => 'required_without:file',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt|max:10240', // 10MB max
        ]);

        $messageData = [
            'chat_id' => $chat_id,
            'sender_id' => $user->id,
        ];

        // إذا كان هناك ملف
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            $filePath = $file->storeAs('messages', $fileName, 'public');

            $messageData['file_path'] = $filePath;
            $messageData['message_type'] = $this->getMessageType($file->getClientOriginalExtension());
            
            // إذا كان هناك نص مع الملف، احفظه في message_content
            if ($request->filled('message_content')) {
                $messageData['message_content'] = $request->message_content;
            } else {
                // إذا لم يكن هناك نص، احفظ اسم الملف كرسالة
                $messageData['message_content'] = $file->getClientOriginalName();
            }
        } else {
            // رسالة نصية فقط
            $messageData['message_content'] = $request->message_content;
            $messageData['message_type'] = 'text';
            $messageData['file_path'] = null;
        }

        $message = Message::create($messageData);

        // تحديث وقت آخر تحديث للمحادثة
        $chat->update(['updated_at' => now()]);

        // إرسال الإشعار عبر WebSocket للمستلم فقط
        try {
            broadcast(new MessageEvent($message));
        } catch (\Exception $e) {
            // تسجيل الخطأ ولكن لا نوقف العملية
            \Log::error('WebSocket broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الرسالة بنجاح',
            'data' => $message->load('sender')
        ], 201);
    }

    /**
     * الحصول على رسائل محادثة مع ترقيم الصفحات
     */
    public function getMessages(Request $request, $chat_id)
    {
        $user = Auth::user();
        
        // التحقق من وجود المحادثة والصلاحية
        $chat = Chat::with(['doctor.user', 'patient.user'])
            ->where('id', $chat_id)
            ->where(function($query) use ($user) {
                if ($user->isDoctor() && $user->doctor) {
                    $query->where('doctor_id', $user->doctor->id);
                } elseif ($user->isPatient() && $user->patient) {
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

        $perPage = $request->get('per_page', 20);
        $messages = Message::with('sender')
            ->where('chat_id', $chat_id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // تحويل الرسائل إلى ترتيب تصاعدي (الأقدم أولاً)
        $messages->getCollection()->transform(function ($message) {
            // إذا كان الملف، أضف رابط التحميل
            if ($message->message_type !== 'text' && $message->file_path) {
                $message->file_url = Storage::url($message->file_path);
            }
            return $message;
        });

        return response()->json([
            'success' => true,
            'chat' => $chat,
            'messages' => $messages
        ]);
    }

    /**
     * حذف رسالة
     */
    public function deleteMessage(Request $request, $chat_id, $message_id)
    {
        $user = Auth::user();
        
        $message = Message::where('id', $message_id)
            ->where('chat_id', $chat_id)
            ->where('sender_id', $user->id)
            ->first();

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'الرسالة غير موجودة أو لا تملك صلاحية حذفها'
            ], 404);
        }

        // حذف الملف إذا كان موجود
        if ($message->message_type !== 'text' && $message->file_path && Storage::disk('public')->exists($message->file_path)) {
            Storage::disk('public')->delete($message->file_path);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الرسالة بنجاح'
        ]);
    }

    /**
     * تحديد نوع الرسالة بناءً على امتداد الملف
     */
    private function getMessageType($extension)
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtensions = ['pdf', 'doc', 'docx', 'txt'];
        
        $extension = strtolower($extension);
        
        if (in_array($extension, $imageExtensions)) {
            return 'image';
        } elseif (in_array($extension, $fileExtensions)) {
            return 'file';
        }
        
        return 'file'; // افتراضي
    }
}
