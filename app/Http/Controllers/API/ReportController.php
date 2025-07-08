<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;

class ReportController extends Controller
{
    public function getReport(Request $request)
    {
        $query = Payment::query();

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has(['from_date', 'to_date'])) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }

        $total = $query->sum('amount');
        $totalFees = $query->sum('fee');
        $netTotal = $query->sum('net_amount');
        $count = $query->count();

        return response()->json([
            'total_payments' => $count,
            'total_amount' => $total,
            'total_fees' => $totalFees,
            'net_amount' => $netTotal,
        ]);
    }
}
