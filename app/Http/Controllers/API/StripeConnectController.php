<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\AccountLink;

class StripeConnectController extends Controller
{
    public function createOnboardingLink($doctor_id)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $doctor = User::findOrFail($doctor_id);

        if (!$doctor->stripe_account_id) {
            return response()->json(['error' => 'Doctor has no Stripe account ID'], 400);
        }

        try {
            $accountLink = AccountLink::create([
                'account' => $doctor->stripe_account_id,
                'refresh_url' => url('/api/stripe/onboard/refresh'),
                'return_url' => url('/api/stripe/onboard/return'),
                'type' => 'account_onboarding',
            ]);

            return response()->json([
                'url' => $accountLink->url
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe onboarding link creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create onboarding link'], 500);
        }
    }
}
