<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Payment>
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state: a completed bank transfer.
     *
     * Factory rows bypass the balance rules on purpose so tests can build
     * edge cases. Tests that exercise the rules use RecordPayment instead.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Tenant::current()?->id,
            'invoice_id' => Invoice::factory(),
            'status_id' => fn () => PaymentStatus::idForKey(PaymentStatus::KEY_COMPLETED),
            'parent_payment_id' => null,
            'type' => PaymentType::Payment,
            'method' => PaymentMethod::BankTransfer,
            'amount' => '10.00',
            'currency' => Payment::DEFAULT_CURRENCY,
            'reference' => null,
            'paid_at' => now()->subDay(),
            'notes' => null,
            'meta' => null,
        ];
    }

    /**
     * State for a payment that is still pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => PaymentStatus::idForKey(PaymentStatus::KEY_PENDING),
        ]);
    }

    /**
     * State for a payment that failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => PaymentStatus::idForKey(PaymentStatus::KEY_FAILED),
        ]);
    }

    /**
     * State for a completed refund of a given payment.
     */
    public function refundOf(Payment $payment, string $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_id' => $payment->invoice_id,
            'parent_payment_id' => $payment->id,
            'type' => PaymentType::Refund,
            'amount' => $amount,
        ]);
    }

    /**
     * State for a soft-deleted payment.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }
}
