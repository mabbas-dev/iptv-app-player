<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Reseller plans (credit-based activation)
        $resellerPlans = [
            ['name' => 'Trial 1 Day', 'plan_type' => 'reseller', 'duration_days' => 1, 'credit_cost' => 0, 'is_trial' => true, 'sort_order' => 1],
            ['name' => 'Trial 3 Days', 'plan_type' => 'reseller', 'duration_days' => 3, 'credit_cost' => 0, 'is_trial' => true, 'sort_order' => 2],
            ['name' => 'Trial 7 Days', 'plan_type' => 'reseller', 'duration_days' => 7, 'credit_cost' => 0, 'is_trial' => true, 'sort_order' => 3],
            ['name' => '1 Month', 'plan_type' => 'reseller', 'duration_days' => 30, 'credit_cost' => 1, 'is_trial' => false, 'sort_order' => 4],
            ['name' => '3 Months', 'plan_type' => 'reseller', 'duration_days' => 90, 'credit_cost' => 3, 'is_trial' => false, 'sort_order' => 5],
            ['name' => '6 Months', 'plan_type' => 'reseller', 'duration_days' => 180, 'credit_cost' => 6, 'is_trial' => false, 'sort_order' => 6],
            ['name' => '12 Months', 'plan_type' => 'reseller', 'duration_days' => 365, 'credit_cost' => 10, 'is_trial' => false, 'sort_order' => 7],
        ];

        foreach ($resellerPlans as $plan) {
            Plan::updateOrCreate(['name' => $plan['name'], 'plan_type' => 'reseller'], $plan + ['is_active' => true]);
        }

        // Customer plans (direct purchase on website)
        $customerPlans = [
            ['name' => '1 Month', 'plan_type' => 'customer', 'duration_days' => 30, 'credit_cost' => 0, 'price_usd' => 4.99, 'is_trial' => false, 'sort_order' => 1],
            ['name' => '3 Months', 'plan_type' => 'customer', 'duration_days' => 90, 'credit_cost' => 0, 'price_usd' => 12.99, 'is_trial' => false, 'sort_order' => 2],
            ['name' => '6 Months', 'plan_type' => 'customer', 'duration_days' => 180, 'credit_cost' => 0, 'price_usd' => 22.99, 'is_trial' => false, 'sort_order' => 3],
            ['name' => '12 Months', 'plan_type' => 'customer', 'duration_days' => 365, 'credit_cost' => 0, 'price_usd' => 39.99, 'is_trial' => false, 'sort_order' => 4],
            ['name' => 'Lifetime', 'plan_type' => 'customer', 'duration_days' => 36500, 'credit_cost' => 0, 'price_usd' => 79.99, 'is_trial' => false, 'is_lifetime' => true, 'sort_order' => 5],
        ];

        foreach ($customerPlans as $plan) {
            Plan::updateOrCreate(['name' => $plan['name'], 'plan_type' => 'customer'], $plan + ['is_active' => true]);
        }
    }
}
