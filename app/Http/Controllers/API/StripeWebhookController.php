<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;



class StripeWebhookController extends Controller
{
    // public function handle(Request $request)
    // {
    //     $payload = @file_get_contents('php://input');
    //     $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    //     $secret = config('services.stripe.webhook_secret');

    //     try {
    //         $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Invalid payload'], 400);
    //     }

    //     if ($event->type === 'payment_intent.succeeded') {

    //         $paymentIntent = $event->data->object;

    //         $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

    //         if ($payment) {
    //             $payment->update([
    //                 'status' => 'succeeded',
    //             ]);

    //             // تحديث حالة الاستشارة إن وجدت
    //             if ($payment->consultation_id) {
    //                 // Consultation::find($payment->consultation_id)->update(['status' => 'paid']);
    //             }
    //         }
    //         Log::info("💳 Payment successful: " . $paymentIntent->id);
    //     }

    //     return response()->json(['status' => 'success']);
    // }

    public function handle()
    {
    }
}
