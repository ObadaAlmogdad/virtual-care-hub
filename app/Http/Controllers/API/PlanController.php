<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\PlanService;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(private PlanService $planService)
    {
        $this->middleware(['auth:sanctum', 'ensure.role:Admin'])->except(['index', 'show']);
    }

    public function index()
    {
        return Plan::where('is_active', true)->get();
    }

    public function show(Plan $plan)
    {
        return $plan;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0',
            'expected_wait_minutes' => 'integer|min:0',
            'private_consultations_quota' => 'integer|min:0',
            'ai_consultations_quota' => 'integer|min:0',
            'max_family_members' => 'integer|min:0',
            'savings_percent' => 'integer|min:0|max:100',
        ]);

        $plan = $this->planService->create($data);
        return response()->json($plan, 201);
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'duration' => 'sometimes|integer|min:1',
            'is_active' => 'sometimes|boolean',
            'priority' => 'sometimes|integer|min:0',
            'expected_wait_minutes' => 'sometimes|integer|min:0',
            'private_consultations_quota' => 'sometimes|integer|min:0',
            'ai_consultations_quota' => 'sometimes|integer|min:0',
            'max_family_members' => 'sometimes|integer|min:0',
            'savings_percent' => 'sometimes|integer|min:0|max:100',
        ]);

        $plan = $this->planService->update($plan, $data);
        return response()->json($plan);
    }

    public function toggle(Plan $plan)
    {
        $plan = $this->planService->toggleActive($plan);
        return response()->json($plan);
    }

    /**
     * حذف خطة
     */
    public function destroy(Plan $plan)
    {
        try {
            // التحقق من وجود اشتراكات نشطة على هذه الخطة
            $activeSubscriptions = \App\Models\Subscription::where('plan_id', $plan->id)
                ->where('status', 'active')
                ->count();

            if ($activeSubscriptions > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف الخطة لوجود ' . $activeSubscriptions . ' اشتراك نشط عليها',
                    'active_subscriptions_count' => $activeSubscriptions
                ], 400);
            }

            // حذف الخطة
            $planName = $plan->name;
            $plan->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الخطة "' . $planName . '" بنجاح',
                'deleted_plan' => [
                    'id' => $plan->id,
                    'name' => $planName
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Plan deletion failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'فشل في حذف الخطة',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}


