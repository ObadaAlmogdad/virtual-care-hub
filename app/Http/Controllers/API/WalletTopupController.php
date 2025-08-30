<?php

namespace App\Http\Controllers\API;

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


    public function testTopup(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1|max:10000',
            ]);

            $user = Auth::user();
            $amount = $request->amount;

            // إنشاء أو تحديث المحفظة
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );

            // إضافة الرصيد إلى المحفظة
            $wallet->increment('balance', $amount);

            // إنشاء معاملة تجريبية
            $transaction = \App\Models\Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'charge',
                'amount' => $amount,
                'reference_id' => 'TEST_' . time() . '_' . $user->id,
                'reference_type' => 'test_topup',
                'description' => 'شحن  للمحفظة ',
            ]);

            // تحديث رصيد المحفظة
            $wallet->refresh();

            return response()->json([
                'success' => true,
                'message' => '✅ تم شحن المحفظة بنجاح ',
                'data' => [
                    'amount_added' => $amount,
                    'new_balance' => $wallet->balance,
                    'transaction_id' => $transaction->id,
                    'wallet_id' => $wallet->id,
                    'user_id' => $user->id,
                    'note' => 'هذه عملية تجريبية للتطوير والاختبار فقط'
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Test wallet topup failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'فشل في شحن المحفظة ',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض رصيد محفظة المستخدم
     */
    public function getBalance(Request $request)
    {
        try {
            $user = Auth::user();
            
            // البحث عن محفظة المستخدم أو إنشاؤها إذا لم تكن موجودة
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );

            // جلب آخر 5 معاملات للمحفظة
            $recentTransactions = $wallet->transactions()
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'wallet_id' => $wallet->id,
                    'user_id' => $user->id,
                    'current_balance' => $wallet->balance,
                    'recent_transactions' => $recentTransactions->map(function ($transaction) {
                        return [
                            'id' => $transaction->id,
                            'type' => $transaction->type,
                            'amount' => $transaction->amount,
                            'description' => $transaction->description,
                            'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                            'reference_type' => $transaction->reference_type
                        ];
                    }),
                    'total_transactions' => $wallet->transactions()->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get wallet balance failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'فشل في جلب رصيد المحفظة',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
