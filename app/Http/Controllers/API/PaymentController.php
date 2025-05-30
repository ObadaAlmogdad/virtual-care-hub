<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\StripeService;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;


class PaymentController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function createIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'doctor_stripe_account_id' => 'required|string',
        ]);

        $intent = $this->stripeService->createPaymentIntent(
            $request->amount,
            $request->doctor_stripe_account_id
        );

        // لنفترض أنك تمرر consultation_id من الفرونت
        $payment = Payment::create([
            'user_id' => auth()->id(),
            'doctor_id' => $doctorId, // احصل عليه حسب منطقك
            'consultation_id' => $request->consultation_id ?? null,
            'stripe_payment_intent_id' => $intent->id,
            'amount' => $request->amount,
            'fee' => round($request->amount * 0.05),
            'net_amount' => $request->amount - round($request->amount * 0.05),
            'status' => 'pending',
        ]);
        return response()->json([
            'client_secret' => $intent->client_secret,
        ]);
    }

    public function refund($paymentId, StripeService $stripeRefundService)
    {
        $payment = Payment::findOrFail($paymentId);

        // تحققات: هل المستخدم يملك هذا الدفع؟ هل تم الدفع؟ هل لم يتم استرداده من قبل؟
        if ($payment->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($payment->status !== 'succeeded' || $payment->is_refunded) {
            return response()->json(['error' => 'Cannot refund this payment'], 422);
        }

        $stripeRefundService->refund($payment->stripe_payment_intent_id);

        $payment->update([
            'is_refunded' => true,
            'refunded_at' => now(),
        ]);

        return response()->json(['message' => 'Refund processed successfully']);
    }
}
