<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use App\Models\VideoCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use App\Events\VideoCallStarted;
use App\Events\VideoCallAccepted;
use App\Events\VideoCallDeclined;
use App\Events\VideoCallEnded;

class VideoCallController extends Controller
{
    public function start(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|integer|exists:chats,id',
        ]);

        $user = Auth::user();

        $executed = RateLimiter::attempt(
            'start-call:'.$user->id,
            $perMinute = 5,
            function () {}
        );
        if (!$executed) {
            return response()->json(['message' => 'Too many attempts. Please try again later.'], 429);
        }

        $chat = Chat::with(['doctor.user', 'patient.user'])->find($request->chat_id);

        if (!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        // تحقق من عضوية المستخدم في المحادثة
        $isMember = false;
        $calleeUserId = null;
        if ($user->isDoctor() && $chat->doctor && $chat->doctor->user_id === $user->id) {
            $isMember = true;
            $calleeUserId = optional($chat->patient)->user_id;
        } elseif ($user->isPatient() && $chat->patient && $chat->patient->user_id === $user->id) {
            $isMember = true;
            $calleeUserId = optional($chat->doctor)->user_id;
        }

        if (!$isMember || !$calleeUserId) {
            return response()->json(['message' => 'Unauthorized for this chat'], 403);
        }

        $channelName = 'consultation_'.$chat->id.'_'.Str::uuid();

        $videoCall = VideoCall::create([
            'chat_id' => $chat->id,
            'caller_id' => $user->id,
            'callee_id' => $calleeUserId,
            'channel_name' => $channelName,
            'agora_uid_caller' => $user->id,
            'agora_uid_callee' => $calleeUserId,
            'status' => 'ringing',
        ]);

        // بث بدء المكالمة للطرف الآخر وقناة الدردشة
        broadcast(new VideoCallStarted($videoCall))->toOthers();

        return response()->json([
            'success' => true,
            'call' => $videoCall,
        ], 201);
    }

    public function accept(Request $request)
    {
        $request->validate([
            'call_id' => 'required|integer|exists:video_calls,id',
        ]);
        $user = Auth::user();
        $call = VideoCall::find($request->call_id);

        if ($call->callee_id !== $user->id) {
            return response()->json(['message' => 'Only callee can accept'], 403);
        }

        if (!in_array($call->status, ['ringing', 'initiated'])) {
            return response()->json(['message' => 'Invalid state'], 422);
        }

        $call->update([
            'status' => 'accepted',
            'started_at' => now(),
        ]);

        // رسالة نظامية اختيارية في المحادثة
        \App\Models\Message::create([
            'chat_id' => $call->chat_id,
            'sender_id' => $user->id,
            'message_content' => 'تم بدء مكالمة فيديو',
            'message_type' => 'text',
        ]);

        broadcast(new VideoCallAccepted($call))->toOthers();

        return response()->json(['success' => true, 'call' => $call]);
    }

    public function decline(Request $request)
    {
        $request->validate([
            'call_id' => 'required|integer|exists:video_calls,id',
        ]);
        $user = Auth::user();
        $call = VideoCall::find($request->call_id);

        if ($call->callee_id !== $user->id) {
            return response()->json(['message' => 'Only callee can decline'], 403);
        }

        if (!in_array($call->status, ['ringing', 'initiated'])) {
            return response()->json(['message' => 'Invalid state'], 422);
        }

        $call->update([
            'status' => 'declined',
        ]);

        \App\Models\Message::create([
            'chat_id' => $call->chat_id,
            'sender_id' => $user->id,
            'message_content' => 'تم رفض مكالمة الفيديو',
            'message_type' => 'text',
        ]);

        broadcast(new VideoCallDeclined($call))->toOthers();

        return response()->json(['success' => true, 'call' => $call]);
    }

    public function end(Request $request)
    {
        $request->validate([
            'call_id' => 'required|integer|exists:video_calls,id',
        ]);
        $user = Auth::user();
        $call = VideoCall::find($request->call_id);

        if (!in_array($user->id, [$call->caller_id, $call->callee_id])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($call->status, ['accepted', 'ringing'])) {
            return response()->json(['message' => 'Invalid state'], 422);
        }

        $endedAt = now();
        $duration = null;
        if ($call->started_at) {
            $duration = $endedAt->diffInSeconds($call->started_at);
        }

        $call->update([
            'status' => 'ended',
            'ended_at' => $endedAt,
            'duration_sec' => $duration,
        ]);

        \App\Models\Message::create([
            'chat_id' => $call->chat_id,
            'sender_id' => $user->id,
            'message_content' => 'تم إنهاء مكالمة الفيديو',
            'message_type' => 'text',
        ]);

        broadcast(new VideoCallEnded($call))->toOthers();

        return response()->json(['success' => true, 'call' => $call]);
    }
}


