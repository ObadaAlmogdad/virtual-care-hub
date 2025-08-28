<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VideoCall;
use App\Services\AgoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoCallTokenController extends Controller
{
    public function getToken(Request $request, AgoraService $agora)
    {
        $request->validate([
            'call_id' => 'required|integer|exists:video_calls,id',
        ]);

        $user = Auth::user();
        $call = VideoCall::find($request->call_id);

        if (!in_array($user->id, [$call->caller_id, $call->callee_id])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $uid = $user->id; // ثابت لكل مستخدم
        $ttl = (int) config('services.agora.token_ttl', 300);

        $token = $agora->generateRtcToken($call->channel_name, $uid, $ttl);
        if (!$token) {
            return response()->json(['message' => 'Token generation failed'], 500);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
            'uid' => $uid,
            'channel_name' => $call->channel_name,
            'app_id' => config('services.agora.app_id'),
            'expires_in' => $ttl,
        ]);
    }

    public function renewToken(Request $request, AgoraService $agora)
    {
        $request->validate([
            'call_id' => 'required|integer|exists:video_calls,id',
        ]);
        return $this->getToken($request, $agora);
    }
}


