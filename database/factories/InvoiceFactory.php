<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contact;
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
            'invoice_number' => 'INV-' . $this->faker->unique()->numerify('######'),
            'company_id' => Company::factory(),
            'contact_id' => Contact::factory(),
            'order_id' => null,
            'status_id' => InvoiceStatus::factory(),
            'issue_date' => $issueDate,
            'due_date' => (clone $issueDate)->modify('+30 days'),
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'total' => $subtotal + $taxTotal,
            'currency' => 'GBP',
            'notes' => $this->faker->optional()->sentence(),
            'meta' => null,
        ];
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => InvoiceStatus::where('title', 'Paid')->value('id'),
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
