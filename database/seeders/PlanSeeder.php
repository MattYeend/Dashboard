<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Services\Plans\StripeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stripeService = app(StripeService::class);

        $plans = [
            [
                'name' => 'Starter',
                'description' => 'Essential features for small teams getting started. Includes core dashboard access, up to 5 projects, and email support.', 
                'price_per_user_per_month' => 2500,
                'is_active' => true,
            ],
            [
                'name' => 'Professional',
                'description' => 'Advanced features and priority support for growing businesses. Unlimited projects, API access, and priority email support.',
                'price_per_user_per_month' => 4900,
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Full feature access, SLA-backed support, SSO, and custom integrations for large organisations.',
                'price_per_user_per_month' => 9900,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $data) {
            $plan = Plan::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                array_merge($data, ['slug' => Str::slug($data['name'])])
            );

            if (! $plan->stripe_product_id) {
                $stripeService->createProductWithPrice($plan);
            }
        }
    }
}
