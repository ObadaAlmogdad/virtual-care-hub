<?php
namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent($amount, $doctorStripeAccountId)
    {
        return PaymentIntent::create([
            'amount' => $amount * 100,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'application_fee_amount' => round($amount * 0.05 * 100),
            'transfer_data' => [
                'destination' => $doctorStripeAccountId,
            ],
        ]);
    }

    public function refund($paymentIntentId)
    {
        return Refund::create([
            'payment_intent' => $paymentIntentId,
        ]);
    }
}
