<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\StripeService;
use App\Models\Payment;
use App\Models\Consultation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function createIntent(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'doctor_id' => 'required|exists:users,id',
                'doctor_stripe_account_id' => 'required|string',
                'consultation_id' => 'nullable|exists:consultations,id',
            ]);

            // التحقق من أن المستخدم هو صاحب الاستشارة
            if ($request->consultation_id) {
                $consultation = Consultation::findOrFail($request->consultation_id);
                if ($consultation->user_id !== Auth::id()) {
                    throw ValidationException::withMessages([
                        'consultation_id' => ['You are not authorized to pay for this consultation']
                    ]);
                }
            }

            $intent = $this->stripeService->createPaymentIntent(
                $request->amount,
                $request->doctor_stripe_account_id
            );

            $payment = Payment::create([
                'user_id' => Auth::id(),
                'doctor_id' => $request->doctor_id,
                'consultation_id' => $request->consultation_id,
                'stripe_payment_intent_id' => $intent->id,
                'amount' => $request->amount,
                'fee' => round($request->amount * 0.05),
                'net_amount' => $request->amount - round($request->amount * 0.05),
                'status' => 'pending',
            ]);

            return response()->json([
                'client_secret' => $intent->client_secret,
                'payment_id' => $payment->id,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Payment intent creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Payment creation failed'], 500);
        }
    }

    public function refund($paymentId, Request $request)
    {
        try {
            $payment = Payment::findOrFail($paymentId);

            // التحقق من الصلاحيات
            if ($payment->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // التحقق من إمكانية الاسترداد
            if (!$payment->canBeRefunded()) {
                return response()->json(['error' => 'Payment cannot be refunded'], 422);
            }

            // التحقق من سبب الاسترداد
            $request->validate([
                'reason' => 'required|string|max:255',
            ]);

            // تنفيذ الاسترداد
            $this->stripeService->refund($payment->stripe_payment_intent_id);

            // تحديث حالة الدفع
            $payment->update([
                'is_refunded' => true,
                'refunded_at' => now(),
                'refund_reason' => $request->reason,
            ]);

            // تحديث حالة الاستشارة إذا وجدت
            if ($payment->consultation_id) {
                Consultation::find($payment->consultation_id)->update(['status' => 'refunded']);
            }

            return response()->json(['message' => 'Refund processed successfully']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Refund failed: ' . $e->getMessage());
            return response()->json(['error' => 'Refund processing failed'], 500);
        }
    }

    public function getPaymentStatus($paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);

            if ($payment->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json([
                'status' => $payment->status,
                'is_refunded' => $payment->is_refunded,
                'refunded_at' => $payment->refunded_at,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get payment status'], 500);
        }
    }
}
