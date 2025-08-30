<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\Consultation;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        try {
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSuccess($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailure($event->data->object);
                    break;

                case 'charge.refunded':
                    $this->handleRefund($event->data->object);
                    break;

                default:
                    Log::info('Unhandled event type: ' . $event->type);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    protected function handlePaymentSuccess($paymentIntent)
    {
        // معالجة شحن المحفظة
        if (isset($paymentIntent->metadata->type) && $paymentIntent->metadata->type == 'wallet_topup') {
            $userId = $paymentIntent->metadata->user_id;
            $amount = $paymentIntent->amount / 100;

            $wallet = \App\Models\Wallet::firstOrCreate(['user_id' => $userId]);
            $wallet->increment('balance', $amount);

            \App\Models\Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'charge',
                'amount' => $amount,
                'reference_id' => $paymentIntent->id,
                'reference_type' => 'stripe_payment_intent',
                'description' => 'Wallet top-up via Stripe',
            ]);

            Log::info("Wallet topped up for user $userId, amount: $amount");
            return;
        }

        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update([
                'status' => 'succeeded',
            ]);

            if ($payment->consultation_id) {
                Consultation::find($payment->consultation_id)->update(['status' => 'paid']);
            }

            Log::info("💳 Payment successful: " . $paymentIntent->id);
        }
    }

    protected function handlePaymentFailure($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update([
                'status' => 'failed',
            ]);

            Log::error("❌ Payment failed: " . $paymentIntent->id);
        }
    }

    protected function handleRefund($charge)
    {
        $payment = Payment::where('stripe_payment_intent_id', $charge->payment_intent)->first();

        if ($payment) {
            $payment->update([
                'is_refunded' => true,
                'refunded_at' => now(),
            ]);

            if ($payment->consultation_id) {
                Consultation::find($payment->consultation_id)->update(['status' => 'refunded']);
            }

            Log::info("🔄 Payment refunded: " . $charge->payment_intent);
        }
    }
}
