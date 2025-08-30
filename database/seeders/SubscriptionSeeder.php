<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subscription;
use App\Models\SubscriptionMember;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Str;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users and plans
        $users = User::where('role', 'Patient')->take(15)->get();
        $plans = Plan::all();

        if ($users->isEmpty() || $plans->isEmpty()) {
            return;
        }

        foreach ($users as $index => $user) {
            $plan = $plans->random();
            $startDate = now()->subDays(rand(1, 90));
            $endDate = $plan->duration ? $startDate->copy()->addDays($plan->duration) : null;
            
            // Randomly set some subscriptions as expired
            $status = $endDate && $endDate->isPast() ? 'expired' : 'active';
            
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'payment_method' => rand(0, 1) ? 'wallet' : 'stripe',
                'payment_id' => rand(1000, 9999),
                'remaining_private_consultations' => $status === 'active' ? rand(0, $plan->private_consultations_quota) : 0,
                'remaining_ai_consultations' => $status === 'active' ? rand(0, $plan->ai_consultations_quota) : 0,
                'family_code' => $this->generateUniqueFamilyCode(),
                'max_family_members' => $plan->max_family_members,
            ]);

            // Add the main user as a member
            SubscriptionMember::create([
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
            ]);

            // Add family members for some subscriptions
            if ($plan->max_family_members > 1 && rand(0, 1)) {
                $additionalMembers = User::where('role', 'Patient')
                    ->where('id', '!=', $user->id)
                    ->take(rand(1, min(3, $plan->max_family_members - 1)))
                    ->get();

                foreach ($additionalMembers as $member) {
                    SubscriptionMember::create([
                        'subscription_id' => $subscription->id,
                        'user_id' => $member->id,
                    ]);
                }
            }
        }
    }

    private function generateUniqueFamilyCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
            $exists = Subscription::where('family_code', $code)->exists();
        } while ($exists);
        return $code;
    }
}
