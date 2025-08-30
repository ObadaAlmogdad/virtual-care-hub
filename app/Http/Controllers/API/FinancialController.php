<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Doctor;
use App\Models\DoctorSpecialty;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialController extends Controller
{
    public function __construct()
    {
        // Temporarily disabled for testing
        // $this->middleware(['auth:sanctum', 'ensure.role:Admin']);
    }

    /**
     * Get financial summary (total revenue, amounts due to platform, amounts paid to doctors)
     */
    public function getFinancialSummary()
    {
        // Get current month and previous month
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();

        // Debug information
        $debug = [
            'current_month_start' => $currentMonth->format('Y-m-d H:i:s'),
            'current_month_end' => $currentMonth->copy()->endOfMonth()->format('Y-m-d H:i:s'),
            'previous_month_start' => $previousMonth->format('Y-m-d H:i:s'),
            'previous_month_end' => $previousMonth->copy()->endOfMonth()->format('Y-m-d H:i:s'),
            'now' => now()->format('Y-m-d H:i:s'),
        ];

        // Current month transactions
        $currentMonthTransactions = Transaction::whereBetween('created_at', [
            $currentMonth, 
            $currentMonth->copy()->endOfMonth()
        ])->get();

        // Previous month transactions
        $previousMonthTransactions = Transaction::whereBetween('created_at', [
            $previousMonth, 
            $previousMonth->copy()->endOfMonth()
        ])->get();

        // Debug transaction counts
        $debug['current_month_total_transactions'] = $currentMonthTransactions->count();
        $debug['previous_month_total_transactions'] = $previousMonthTransactions->count();

        // Calculate current month totals
        $currentMonthPaidToDoctors = $currentMonthTransactions
            ->where('type', 'charge')
            ->filter(function($transaction) {
                return stripos($transaction->description, 'Appointment') !== false;
            })
            ->sum('amount');

        $currentMonthPlatformRevenue = $currentMonthTransactions
            ->where('type', 'payment')
            ->filter(function($transaction) {
                return stripos($transaction->description, 'Appointment') !== false;
            })
            ->sum('amount') * 0.15; // 15% platform fee

        $currentMonthTotalRevenue = $currentMonthTransactions
            ->where('type', 'payment')
            ->filter(function($transaction) {
                return stripos($transaction->description, 'Appointment') !== false;
            })
            ->sum('amount');

        // Calculate previous month totals
        $previousMonthPaidToDoctors = $previousMonthTransactions
            ->where('type', 'charge')
            ->filter(function($transaction) {
                return stripos($transaction->description, 'Appointment') !== false;
            })
            ->sum('amount');

        $previousMonthPlatformRevenue = $previousMonthTransactions
            ->where('type', 'payment')
            ->filter(function($transaction) {
                return stripos($transaction->description, 'Appointment') !== false;
            })
            ->sum('amount') * 0.15;

        $previousMonthTotalRevenue = $previousMonthTransactions
            ->where('type', 'payment')
            ->filter(function($transaction) {
                return stripos($transaction->description, 'Appointment') !== false;
            })
            ->sum('amount');

        // Debug filtered counts
        $debug['current_month_appointment_payments'] = $currentMonthTransactions
            ->where('type', 'payment')
            ->filter(function($transaction) {
                return stripos($transaction->description, 'Appointment') !== false;
            })
            ->count();
        $debug['current_month_appointment_charges'] = $currentMonthTransactions
            ->where('type', 'charge')
            ->filter(function($transaction) {
                return stripos($transaction->description, 'Appointment') !== false;
            })
            ->count();

        // Calculate percentage changes
        $doctorsPaymentChange = $previousMonthPaidToDoctors > 0 
            ? (($currentMonthPaidToDoctors - $previousMonthPaidToDoctors) / $previousMonthPaidToDoctors) * 100 
            : 0;

        $platformRevenueChange = $previousMonthPlatformRevenue > 0 
            ? (($currentMonthPlatformRevenue - $previousMonthPlatformRevenue) / $previousMonthPlatformRevenue) * 100 
            : 0;

        $totalRevenueChange = $previousMonthTotalRevenue > 0 
            ? (($currentMonthTotalRevenue - $previousMonthTotalRevenue) / $previousMonthTotalRevenue) * 100 
            : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'amounts_paid_to_doctors' => [
                    'amount' => round($currentMonthPaidToDoctors, 2),
                    'change_percentage' => round($doctorsPaymentChange, 1),
                    'change_direction' => $doctorsPaymentChange >= 0 ? 'increase' : 'decrease'
                ],
                'amounts_due_to_platform' => [
                    'amount' => round($currentMonthPlatformRevenue, 2),
                    'change_percentage' => round($platformRevenueChange, 1),
                    'change_direction' => $platformRevenueChange >= 0 ? 'increase' : 'decrease'
                ],
                'total_revenue' => [
                    'amount' => round($currentMonthTotalRevenue, 2),
                    'change_percentage' => round($totalRevenueChange, 1),
                    'change_direction' => $totalRevenueChange >= 0 ? 'increase' : 'decrease'
                ]
            ],
            'debug' => $debug
        ]);
    }

    /**
     * Get monthly revenue trends for the last 6 months
     */
    public function getMonthlyRevenue()
    {
        $months = [];
        $revenues = [];
        $debug = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('F'); // January, February, etc.
            
            // Clone the month to avoid modifying the original
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $monthRevenue = Transaction::whereBetween('created_at', [
                $monthStart,
                $monthEnd
            ])
            ->where('type', 'payment')
            ->where('description', 'like', '%Appointment%')
            ->sum('amount');

            $revenues[] = round($monthRevenue, 2);
            
            // Debug info for each month
            $debug[] = [
                'month' => $month->format('F Y'),
                'start' => $monthStart->format('Y-m-d'),
                'end' => $monthEnd->format('Y-m-d'),
                'revenue' => round($monthRevenue, 2),
                'transaction_count' => Transaction::whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where('type', 'payment')
                    ->where('description', 'like', '%Appointment%')
                    ->count()
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'months' => $months,
                'revenues' => $revenues
            ],
            'debug' => $debug
        ]);
    }

    /**
     * Get revenue breakdown by medical specialty
     */
    public function getRevenueBySpecialty()
    {
        $specialtyRevenue = DB::table('transactions as t')
            ->join('wallets as w', 't.wallet_id', '=', 'w.id')
            ->join('users as u', 'w.user_id', '=', 'u.id')
            ->join('doctors as d', 'u.id', '=', 'd.user_id')
            ->join('doctor_specialties as ds', 'd.id', '=', 'ds.doctor_id')
            ->join('medical_tags as mt', 'ds.medical_tag_id', '=', 'mt.id')
            ->where('t.type', 'charge')
            ->where('t.description', 'like', '%Appointment%')
            ->where('ds.is_active', true)
            ->select(
                'mt.name as specialty_name',
                'mt.name_ar as specialty_name_ar',
                DB::raw('SUM(t.amount) as total_revenue'),
                DB::raw('COUNT(*) as consultation_count')
            )
            ->groupBy('mt.id', 'mt.name', 'mt.name_ar')
            ->orderByDesc('total_revenue')
            ->get();

        $totalRevenue = $specialtyRevenue->sum('total_revenue');

        $result = $specialtyRevenue->map(function ($item) use ($totalRevenue) {
            $percentage = $totalRevenue > 0 ? round(($item->total_revenue / $totalRevenue) * 100, 1) : 0;
            
            return [
                'specialty_name' => $item->specialty_name,
                'specialty_name_ar' => $item->specialty_name_ar,
                'total_revenue' => round($item->total_revenue, 2),
                'consultation_count' => $item->consultation_count,
                'percentage' => $percentage
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    /**
     * Get financial transaction records for doctors
     */
    public function getTransactionRecords(Request $request)
    {
        $query = DB::table('transactions as t')
            ->join('wallets as w', 't.wallet_id', '=', 'w.id')
            ->join('users as u', 'w.user_id', '=', 'u.id')
            ->join('doctors as d', 'u.id', '=', 'd.user_id')
            ->join('doctor_specialties as ds', 'd.id', '=', 'ds.doctor_id')
            ->join('medical_tags as mt', 'ds.medical_tag_id', '=', 'mt.id')
            ->where('t.type', 'charge')
            ->where('t.description', 'like', '%Appointment%')
            ->where('ds.is_active', true);

        // Search by doctor name or specialty
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('u.fullName', 'like', "%{$search}%")
                  ->orWhere('mt.name', 'like', "%{$search}%")
                  ->orWhere('mt.name_ar', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('t.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('t.created_at', '<=', $request->date_to);
        }

        // Filter by payment status (if needed)
        if ($request->filled('payment_status')) {
            // This could be extended based on your payment status logic
        }

        $transactions = $query->select(
            't.id',
            'u.fullName as doctor_name',
            'mt.name as specialty_name',
            'mt.name_ar as specialty_name_ar',
            DB::raw('COUNT(*) as consultation_count'),
            DB::raw('15 as commission_rate'), // Fixed 15% commission
            DB::raw('SUM(t.amount) as amount_due'),
            't.created_at as addition_date',
            DB::raw("'تم الدفع' as payment_status") // Default status
        )
        ->groupBy('t.id', 'u.fullName', 'mt.name', 'mt.name_ar', 't.created_at')
        ->orderByDesc('t.created_at')
        ->paginate(20);

        $result = $transactions->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'doctor_name' => $item->doctor_name,
                'specialty_name' => $item->specialty_name,
                'specialty_name_ar' => $item->specialty_name_ar,
                'consultation_count' => $item->consultation_count,
                'commission_rate' => $item->commission_rate . '%',
                'amount_due' => round($item->amount_due, 2),
                'addition_date' => Carbon::parse($item->addition_date)->format('Y-m-d'),
                'payment_status' => $item->payment_status
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $result,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ]
        ]);
    }

    /**
     * Debug method to check transactions
     */
    public function debugTransactions()
    {
        // Temporarily remove auth requirement for debugging
        // $this->middleware(['auth:sanctum', 'ensure.role:Admin']);
        
        $totalTransactions = Transaction::count();
        $appointmentTransactions = Transaction::where('description', 'like', '%Appointment%')->count();
        $paymentTransactions = Transaction::where('type', 'payment')->where('description', 'like', '%Appointment%')->count();
        $chargeTransactions = Transaction::where('type', 'charge')->where('description', 'like', '%Appointment%')->count();
        
        $sampleTransactions = Transaction::where('description', 'like', '%Appointment%')
            ->take(5)
            ->get(['id', 'type', 'amount', 'description', 'created_at']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_transactions' => $totalTransactions,
                'appointment_transactions' => $appointmentTransactions,
                'payment_transactions' => $paymentTransactions,
                'charge_transactions' => $chargeTransactions,
                'sample_transactions' => $sampleTransactions,
                'current_month' => now()->format('F Y'),
                'previous_month' => now()->subMonth()->format('F Y'),
            ]
        ]);
    }
}
