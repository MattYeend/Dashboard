<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(5000, 500000);
        $taxTotal = (int) round($subtotal * 0.2);
        $issueDate = $this->faker->dateTimeBetween('-3 months', 'now');

        return [
            'invoice_number' => 'INV-'.$this->faker->unique()->numerify('######'),
            'company_id' => Company::factory(),
            'order_id' => null,
            'status_id' => InvoiceStatus::factory(),
            'issue_date' => $issueDate,
            'due_date' => (clone $issueDate)->modify('+30 days'),
            'sent_at' => null,
            'paid_at' => null,
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'total' => $subtotal + $taxTotal,
            'currency' => 'GBP',
            'notes' => $this->faker->optional()->sentence(),
            'meta' => null,
        ];
    }

    /**
     * Create the invoice's required contact after the model is created.
     *
     * The contact is stored via the polymorphic contacts table, not a
     * foreign key, so it can't be set in definition() and must be
     * attached via the morphOne relation once the invoice exists.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Invoice $invoice): void {
            if ($invoice->contact === null) {
                $invoice->contact()->create([
                    'phone' => $this->faker->phoneNumber(),
                    'email' => $this->faker->companyEmail(),
                    'address' => $this->faker->streetAddress(),
                    'city' => $this->faker->city(),
                    'postal_code' => $this->faker->postcode(),
                    'country' => 'United Kingdom',
                ]);
            }
        });
    }

    /**
     * Indicate that the invoice has been sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => InvoiceStatus::where('title', 'Sent')->value('id'),
            'sent_at' => now()->subDays(5),
        ]);
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => InvoiceStatus::where('title', 'Paid')->value('id'),
            'sent_at' => now()->subDays(10),
            'paid_at' => now()->subDays(2),
            'due_date' => now()->subDays(10),
        ]);
    }

    /**
     * Indicate that the invoice is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => InvoiceStatus::where('title', 'Overdue')->value('id'),
            'sent_at' => now()->subDays(20),
            'due_date' => now()->subDays(14),
        ]);
    }

    /**
     * Indicate that the invoice is soft-deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }
}
