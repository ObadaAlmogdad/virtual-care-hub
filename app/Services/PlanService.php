<?php

namespace App\Services;

use App\Models\Plan;

class PlanService
{
    public function create(array $data): Plan
    {
        return Plan::create($data);
    }

    public function update(Plan $plan, array $data): Plan
    {
        $plan->update($data);
        return $plan;
    }

    public function toggleActive(Plan $plan): Plan
    {
        $plan->is_active = !$plan->is_active;
        $plan->save();
        return $plan;
    }
}


