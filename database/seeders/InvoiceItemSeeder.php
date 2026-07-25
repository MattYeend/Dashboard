<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceItemSeeder extends Seeder
{
    public function run(): void
    {
        if (InvoiceItem::exists()) {
            $this->command->info('Invoice Items already seeded, skipping...');

            return;
        }

        $invoices = Invoice::whereIn('invoice_number', [
            'INV-000001',
            'INV-000002',
            'INV-000003',
            'INV-000004',
            'INV-000005',
            'INV-000006',
        ])->get()->keyBy('invoice_number');

        if ($invoices->isEmpty()) {
            $this->command->warn('No invoices found, skipping invoice item seeding...');

            return;
        }

        $creator = User::first();

        if ($creator === null) {
            $this->command->warn('No users found, skipping invoice item seeding...');

            return;
        }

        $items = [
            'INV-000001' => [
                ['description' => 'Bespoke CRM development, phase one, sprint 1', 'quantity' => 1, 'unit_price' => 60000, 'tax_rate' => 20.00, 'total' => 72000],
                ['description' => 'Bespoke CRM development, phase one, sprint 2', 'quantity' => 1, 'unit_price' => 60000, 'tax_rate' => 20.00, 'total' => 72000],
            ],
            'INV-000002' => [
                ['description' => 'Bespoke CRM development, phase two, sprint 1', 'quantity' => 1, 'unit_price' => 45000, 'tax_rate' => 20.00, 'total' => 54000],
                ['description' => 'Bespoke CRM development, phase two, sprint 2', 'quantity' => 1, 'unit_price' => 40000, 'tax_rate' => 20.00, 'total' => 48000],
            ],
            'INV-000003' => [
                ['description' => 'Point-of-sale system support retainer, May', 'quantity' => 1, 'unit_price' => 50000, 'tax_rate' => 20.00, 'total' => 60000],
            ],
            'INV-000004' => [
                ['description' => 'Point-of-sale system support retainer, June', 'quantity' => 1, 'unit_price' => 50000, 'tax_rate' => 20.00, 'total' => 60000],
            ],
            'INV-000005' => [
                ['description' => 'Site management software licence, Q2 (per-seat, 10 seats)', 'quantity' => 10, 'unit_price' => 20000, 'tax_rate' => 20.00, 'total' => 240000],
                ['description' => 'Site management software support add-on, Q2', 'quantity' => 1, 'unit_price' => 120000, 'tax_rate' => 20.00, 'total' => 144000],
            ],
            'INV-000006' => [
                ['description' => 'Colocation billing integration, development work', 'quantity' => 1, 'unit_price' => 210000, 'tax_rate' => 20.00, 'total' => 252000],
            ],
        ];

        foreach ($items as $invoiceNumber => $lines) {
            $invoice = $invoices->get($invoiceNumber);

            if (! $invoice) {
                continue;
            }

            foreach ($lines as $position => $line) {
                InvoiceItem::updateOrCreate(
                    [
                        'invoice_id' => $invoice->id,
                        'description' => $line['description'],
                    ],
                    [
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'tax_rate' => $line['tax_rate'],
                        'total' => $line['total'],
                        'position' => $position,
                        'created_by' => $creator->id,
                    ]
                );
            }
        }
    }
}
