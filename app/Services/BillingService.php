<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionMember;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingService
{
    /**
     * Process payment for an appointment-like service.
     * 1) If patient has active subscription with remaining_private_consultations > 0, decrement and credit doctor.
     * 2) Otherwise, deduct from patient wallet; credit doctor wallet.
     * Throws ValidationException if insufficient funds.
     */
    public function processAppointmentPayment(
        int $patientUserId,
        int $doctorUserId,
        float $amount,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): void {
        DB::transaction(function () use ($patientUserId, $doctorUserId, $amount, $referenceId, $referenceType) {
            $subscription = Subscription::query()
                ->where('status', 'active')
                ->where(function ($q) use ($patientUserId) {
                    $q->where('user_id', $patientUserId)
                      ->orWhereExists(function ($sq) use ($patientUserId) {
                          $sq->selectRaw('1')
                              ->from('subscription_members')
                              ->whereColumn('subscription_members.subscription_id', 'subscriptions.id')
                              ->where('subscription_members.user_id', $patientUserId);
                      });
                })
                ->when(true, function ($q) {
                    $q->where(function ($qq) {
                        $qq->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
                    });
                })
                ->lockForUpdate()
                ->first();

            // Case 1: Cover via subscription quota
            if ($subscription && $subscription->remaining_private_consultations > 0) {
                $subscription->remaining_private_consultations -= 1;
                $subscription->save();

                // Credit doctor's wallet from platform (no patient deduction)
                $doctorWallet = Wallet::firstOrCreate(['user_id' => $doctorUserId], ['balance' => 0]);
                $doctorWallet->balance = bcadd((string) $doctorWallet->balance, (string) $amount, 2);
                $doctorWallet->save();

                Transaction::create([
                    'wallet_id' => $doctorWallet->id,
                    'type' => 'charge', // incoming to doctor
                    'amount' => $amount,
                    'reference_id' => $referenceId ? (string) $referenceId : null,
                    'reference_type' => $referenceType,
                    'description' => 'Appointment paid by subscription quota',
                ]);

                return;
            }

            // Case 2: Deduct from patient wallet
            $patientWallet = Wallet::where('user_id', $patientUserId)->lockForUpdate()->first();
            if (!$patientWallet || $patientWallet->balance < $amount) {
                throw ValidationException::withMessages([
                    'wallet' => ['رصيد غير كافٍ لإتمام العملية'],
                ]);
            }

            $patientWallet->balance = bcsub((string) $patientWallet->balance, (string) $amount, 2);
            $patientWallet->save();

            Transaction::create([
                'wallet_id' => $patientWallet->id,
                'type' => 'payment', // outgoing from patient
                'amount' => $amount,
                'reference_id' => $referenceId ? (string) $referenceId : null,
                'reference_type' => $referenceType,
                'description' => 'Appointment payment',
            ]);

            $doctorWallet = Wallet::firstOrCreate(['user_id' => $doctorUserId], ['balance' => 0]);
            $doctorWallet->balance = bcadd((string) $doctorWallet->balance, (string) $amount, 2);
            $doctorWallet->save();

            Transaction::create([
                'wallet_id' => $doctorWallet->id,
                'type' => 'charge', // incoming to doctor
                'amount' => $amount,
                'reference_id' => $referenceId ? (string) $referenceId : null,
                'reference_type' => $referenceType,
                'description' => 'Appointment payment received',
            ]);
        });
    }
}


