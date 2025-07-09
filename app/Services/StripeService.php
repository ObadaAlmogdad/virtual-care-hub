<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Exception\ApiErrorException;
use Stripe\AccountLink;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent($amount, $doctorStripeAccountId)
    {
        try {
            return PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'application_fee_amount' => round($amount * 0.05 * 100),
                'transfer_data' => [
                    'destination' => $doctorStripeAccountId,
                ],
                'metadata' => [
                    'platform_fee_percentage' => '5%',
                ],
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe PaymentIntent creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function refund($paymentIntentId, $amount = null)
    {
        try {
            $refundData = ['payment_intent' => $paymentIntentId];

            if ($amount) {
                $refundData['amount'] = $amount * 100;
            }

            return Refund::create($refundData);
        } catch (ApiErrorException $e) {
            Log::error('Stripe refund failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPaymentIntent($paymentIntentId)
    {
        try {
            return PaymentIntent::retrieve($paymentIntentId);
        } catch (ApiErrorException $e) {
            Log::error('Stripe PaymentIntent retrieval failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function cancelPaymentIntent($paymentIntentId)
    {
        try {
            $intent = PaymentIntent::retrieve($paymentIntentId);
            return $intent->cancel();
        } catch (ApiErrorException $e) {
            Log::error('Stripe PaymentIntent cancellation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createAccountLink($accountId)
    {
        return AccountLink::create([
            'account' => $accountId,
            'refresh_url' => route('stripe.refresh'), // عند فشل العملية يرجع المستخدم هنا
            'return_url' => route('stripe.return'),   // عند النجاح يرجع المستخدم هنا
            'type' => 'account_onboarding',
        ]);
    }

    public function createWalletTopupIntent($amount, $userId)
    {
        try {
            return PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'type' => 'wallet_topup',
                    'user_id' => $userId,
                ],
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Wallet Topup Intent creation failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
