<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\Concerns\InteractsWithTenants;
use Tests\TestCase;

class OrderCurrencyTest extends TestCase
{
    use InteractsWithTenants;
    use RefreshDatabase;

    public function test_order_currency_is_persisted(): void
    {
        $own = $this->makeOrganisation();
        $user = $this->makeMember($own);
        $company = $own->execute(fn () => Company::factory()->create());

        $currency = collect(config('currencies.allowed'))
            ->first(fn ($code) => $code !== 'GBP');

        $this->authenticateAs($user, $own, 'spa')
            ->postJson('/api/orders', [
                'orderable_type' => 'company',
                'orderable_id' => $company->id,
                'title' => 'Currency check',
                'subtotal' => 100,
                'total_amount' => 120,
                'currency' => $currency,
            ])
            ->assertSuccessful();

        $this->assertDatabaseHas('orders', [
            'title' => 'Currency check',
            'currency' => $currency,
        ]);
    }
}
