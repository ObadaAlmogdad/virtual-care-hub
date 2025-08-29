<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionMember;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    /**
     * Patient buys a plan using wallet balance.
     */
    public function subscribeWithWallet(int $userId, Plan $plan): Subscription
    {
        return DB::transaction(function () use ($userId, $plan) {
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();
            if (!$wallet || $wallet->balance < $plan->price) {
                throw ValidationException::withMessages([
                    'wallet' => ['Insufficient wallet balance'],
                ]);
            }

            $wallet->balance = bcsub((string) $wallet->balance, (string) $plan->price, 2);
            $wallet->save();

            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'payment',
                'amount' => $plan->price,
                'reference_id' => (string) $plan->id,
                'reference_type' => Plan::class,
                'description' => 'Plan subscription purchase',
            ]);

            $startDate = now();
            $endDate = $plan->duration ? now()->copy()->addDays($plan->duration) : null;

            $subscription = Subscription::create([
                'user_id' => $userId,
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'payment_method' => 'wallet',
                'payment_id' => null,
                'remaining_private_consultations' => $plan->private_consultations_quota,
                'remaining_ai_consultations' => $plan->ai_consultations_quota,
                'family_code' => $this->generateUniqueFamilyCode(),
                'max_family_members' => $plan->max_family_members,
            ]);

            SubscriptionMember::create([
                'subscription_id' => $subscription->id,
                'user_id' => $userId,
            ]);

            return $subscription;
        });
    }

    /**
     * Join existing subscription by family code.
     */
    public function joinByFamilyCode(int $userId, string $familyCode): Subscription
    {
        return DB::transaction(function () use ($userId, $familyCode) {
            $subscription = Subscription::where('family_code', $familyCode)
                ->where('status', 'active')
                ->lockForUpdate()
                ->firstOrFail();

            $currentCount = SubscriptionMember::where('subscription_id', $subscription->id)->count();
            if ($subscription->max_family_members > 0 && $currentCount >= $subscription->max_family_members) {
                throw ValidationException::withMessages([
                    'family_code' => ['Family members limit reached'],
                ]);
            }

            $alreadyMember = SubscriptionMember::where('subscription_id', $subscription->id)
                ->where('user_id', $userId)
                ->exists();
            if ($alreadyMember) {
                return $subscription;
            }

            SubscriptionMember::create([
                'subscription_id' => $subscription->id,
                'user_id' => $userId,
            ]);

            return $subscription;
        });
    }

    protected function generateUniqueFamilyCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
            $exists = Subscription::where('family_code', $code)->exists();
        } while ($exists);
        return $code;
    }
}


