<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Quote;
use App\Models\QuoteStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Quote>
     */
    protected $model = Quote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 5000);
        $discount = $this->faker->randomFloat(2, 0, $subtotal * 0.1);
        $tax = round(($subtotal - $discount) * 0.2, 2);
        $total = round($subtotal - $discount + $tax, 2);

        return [
            'organisation_id' => Tenant::current()?->id,
            'quotable_type' => (new Company)->getMorphClass(),
            'quotable_id' => Company::factory(),
            'quote_number' => 'QUO-'.strtoupper(
                $this->faker->unique()->bothify('####-????')
            ),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'notes' => $this->faker->boolean(40)
                ? $this->faker->sentence()
                : null,
            'terms' => 'Payment due within 30 days of the order date.',
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'currency' => 'GBP',
            'issued_at' => now(),
            'valid_until' => now()->addDays(30),
            'status_id' => QuoteStatus::query()
                ->where('system_key', QuoteStatus::KEY_DRAFT)
                ->value('id'),
            'meta' => null,
        ];
    }

    /**
     * Associate the quote with a given customer model.
     */
    public function forModel(Model $model): static
    {
        return $this->state(fn (array $attributes) => [
            'quotable_type' => $model->getMorphClass(),
            'quotable_id' => $model->getKey(),
        ]);
    }

    /**
     * Put the quote in a given system status.
     */
    public function withStatus(string $systemKey): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => QuoteStatus::idForKey($systemKey),
        ]);
    }

    /**
     * State for a soft-deleted quote.
     */
    public function deleted(?User $deletedBy = null): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => $deletedBy?->id,
        ]);
    }
}
