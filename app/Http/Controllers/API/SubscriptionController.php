<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
        $this->middleware(['auth:sanctum']);
    }

    public function my()
    {
        $userId = Auth::id();
        return Subscription::with('plan')
            ->whereHas('members', fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('id')
            ->first();
    }

    public function subscribeWithWallet(Request $request, Plan $plan)
    {
        $subscription = $this->subscriptionService->subscribeWithWallet(Auth::id(), $plan);
        return response()->json($subscription->load('plan'), 201);
    }

    public function joinByCode(Request $request)
    {
        $data = $request->validate([
            'family_code' => 'required|string',
        ]);
        $subscription = $this->subscriptionService->joinByFamilyCode(Auth::id(), $data['family_code']);
        return response()->json($subscription->load('plan'));
    }
}


