<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Payments\RecordPayment;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\Invoices\BalanceService;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actor = User::query()->orderBy('id')->first();

        if ($actor === null) {
            return;
        }

        $invoices = Invoice::query()
            ->with('status')
            ->whereHas('status', fn ($status) => $status->whereIn('title', BalanceService::PAYABLE_STATUSES))
            ->orderBy('id')
            ->limit(3)
            ->get();

        foreach ($invoices->values() as $index => $invoice) {
            $plan = $this->plans()[$index] ?? null;

            if ($plan === null || Payment::withTrashed()->where('reference', $plan['reference'])->exists()) {
                continue;
            }

            app(RecordPayment::class)->handle([
                'invoice_id' => $invoice->id,
                'method' => $plan['method']->value,
                'amount' => bcmul(app(BalanceService::class)->poundsFromPence($invoice->total), $plan['share'], 2),
                'paid_at' => now()->subDays($plan['days_ago']),
                'reference' => $plan['reference'],
                'notes' => $plan['notes'],
            ], $actor);
        }
    }

    /**
     * Get the payments to record, as a share of each invoice total.
     *
     * @return array<int, array{share: string, method: PaymentMethod, days_ago: int, reference: string, notes: string}>
     */
    private function plans(): array
    {
        return [
            [
                'share' => '0.50',
                'method' => PaymentMethod::BankTransfer,
                'days_ago' => 12,
                'reference' => 'BACS-DEPOSIT-0001',
                'notes' => 'Fifty per cent deposit received by bank transfer.',
            ],
            [
                'share' => '1.00',
                'method' => PaymentMethod::DirectDebit,
                'days_ago' => 5,
                'reference' => 'DD-MONTHLY-0001',
                'notes' => 'Paid in full by direct debit.',
            ],
            [
                'share' => '0.25',
                'method' => PaymentMethod::Cheque,
                'days_ago' => 2,
                'reference' => 'CHQ-004217',
                'notes' => 'Part payment by cheque.',
            ],
        ];
    }
}
