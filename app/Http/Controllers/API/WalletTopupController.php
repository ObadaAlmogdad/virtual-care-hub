<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\StripeService;
use App\Models\Wallet;
use Illuminate\Validation\ValidationException;
use Stripe\PaymentIntent;

class WalletTopupController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function topup(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1',
            ]);
            $user = Auth::user();
            $intent = $this->stripeService->createWalletTopupIntent($request->amount, $user->id);

            $wallet = \App\Models\Wallet::firstOrCreate(['user_id' => $user->id]);
            \App\Models\Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'charge',
                'amount' => $request->amount,
                'reference_id' => $intent->id,
                'reference_type' => 'stripe_payment_intent',
                'description' => 'Wallet top-up initiated (pending)',
            ]);

            return response()->json([
                'client_secret' => $intent->client_secret,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Wallet topup intent creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Wallet topup creation failed'], 500);
        }
    }

    public function confirmTopup(Request $request)
    {
        try {
            $request->validate([
                'payment_intent_id' => 'required|string',
                'payment_method_id' => 'required|string',
            ]);

            $intent = PaymentIntent::retrieve($request->payment_intent_id);

            $confirmedIntent = $intent->confirm([
                'payment_method' => $request->payment_method_id,
            ]);

            if ($confirmedIntent->status === 'succeeded') {
                // هنا تضيف الرصيد إلى محفظة المستخدم مثلاً
                $user = Auth::user();
                $amount = $confirmedIntent->amount / 100;

             
                Wallet::where('user_id', $user->id)->increment('balance', $amount);

                return response()->json([
                    'message' => '✅ تمت عملية شحن المحفظة بنجاح',
                    'amount' => $amount,
                ]);
            } else {
                return response()->json([
                    'message' => '⚠️ لم تنجح عملية الدفع بعد. الحالة الحالية: ' . $confirmedIntent->status,
                ], 400);
            }
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Wallet topup confirmation failed: ' . $e->getMessage());
            return response()->json(['error' => 'فشل تأكيد الدفع'], 500);
        }
    }
}
