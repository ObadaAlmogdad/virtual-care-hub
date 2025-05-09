<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ActivationRequestService;
use App\Models\User;
use App\Models\ActivationRequest;
use Illuminate\Validation\ValidationException;

class ActivationRequestController extends Controller
{
    protected ActivationRequestService $activationRequestService;

    public function __construct(ActivationRequestService $activationRequestService)
    {
        $this->activationRequestService = $activationRequestService;
    }

    // إرسال طلب تفعيل
    public function send(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $activationRequest = $this->activationRequestService->sendRequest($user);
        return response()->json(['activation_request' => $activationRequest], 201);
    }

    // موافقة المدير على الطلب
    public function approve(Request $request, $activationRequestId)
    {
        $activationRequest = ActivationRequest::findOrFail($activationRequestId);
        $adminId = $request->user()->id; // يفترض أن المدير مسجل دخول
        $notes = $request->input('notes');
        $this->activationRequestService->approveRequest($activationRequest, $adminId, $notes);
        return response()->json(['message' => 'تمت الموافقة على الطلب'], 200);
    }

    // استعلام حالة التفعيل
    public function status($userId)
    {
        $user = User::findOrFail($userId);
        $activationRequest = $this->activationRequestService->getStatus($user);
        return response()->json(['activation_status' => $activationRequest], 200);
    }
}
