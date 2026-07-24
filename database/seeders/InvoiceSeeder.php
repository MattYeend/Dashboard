<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        if (Invoice::exists()) {
            $this->command->info('Invoices already seeded, skipping...');

            return;
        }

        $companies = Company::whereIn('slug', [
            'brightwave-software-ltd',
            'thistle-oak-retail-group',
            'kestrel-build-contractors-ltd',
            'harrogate-data-centres-ltd',
        ])->get()->keyBy('slug');

        if ($companies->isEmpty()) {
            $this->command->warn('No companies found, skipping invoice seeding...');

            return;
        }

        $statuses = InvoiceStatus::all()->keyBy('title');

        if ($statuses->isEmpty()) {
            $this->command->warn('No invoice statuses found, skipping invoice seeding...');

            return;
        }

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found, skipping invoice seeding...');

            return;
        }

        $creator = $users->first();

        $invoices = [
            [
                'invoice_number' => 'INV-000001',
                'company_slug' => 'brightwave-software-ltd',
                'status_title' => 'Paid',
                'issue_date' => '2026-04-01',
                'due_date' => '2026-05-01',
                'sent_at' => '2026-04-01 09:15:00',
                'paid_at' => '2026-04-22 14:03:00',
                'subtotal' => 120000,
                'tax_total' => 24000,
                'total' => 144000,
                'notes' => 'Bespoke CRM development, phase one.',
                'contact' => [
                    'phone' => '01132 496821',
                    'email' => 'accounts@brightwavesoftware.co.uk',
                ],
                'address' => [
                    'address_line_one' => '14 Wellington Street',
                    'city' => 'Leeds',
                    'postcode' => 'LS1 4DL',
                    'country' => 'United Kingdom',
                    'is_primary' => true,
                ],
            ],
            [
                'invoice_number' => 'INV-000002',
                'company_slug' => 'brightwave-software-ltd',
                'status_title' => 'Sent',
                'issue_date' => '2026-06-15',
                'due_date' => '2026-07-15',
                'sent_at' => '2026-06-15 10:42:00',
                'paid_at' => null,
                'subtotal' => 85000,
                'tax_total' => 17000,
                'total' => 102000,
                'notes' => 'Bespoke CRM development, phase two.',
                'contact' => [
                    'phone' => '01132 496821',
                    'email' => 'accounts@brightwavesoftware.co.uk',
                ],
                'address' => [
                    'address_line_one' => '14 Wellington Street',
                    'city' => 'Leeds',
                    'postcode' => 'LS1 4DL',
                    'country' => 'United Kingdom',
                    'is_primary' => true,
                ],
            ],
            [
                'invoice_number' => 'INV-000003',
                'company_slug' => 'thistle-oak-retail-group',
                'status_title' => 'Overdue',
                'issue_date' => '2026-05-01',
                'due_date' => '2026-06-01',
                'sent_at' => '2026-05-01 11:20:00',
                'paid_at' => null,
                'subtotal' => 50000,
                'tax_total' => 10000,
                'total' => 60000,
                'notes' => 'Point-of-sale system support retainer, May.',
                'contact' => [
                    'phone' => '01142 738455',
                    'email' => 'finance@thistleoakretail.co.uk',
                ],
                'address' => [
                    'address_line_one' => '2 Fargate',
                    'city' => 'Sheffield',
                    'postcode' => 'S1 2HE',
                    'country' => 'United Kingdom',
                    'is_primary' => true,
                ],
            ],
            [
                'invoice_number' => 'INV-000004',
                'company_slug' => 'thistle-oak-retail-group',
                'status_title' => 'Draft',
                'issue_date' => null,
                'due_date' => null,
                'sent_at' => null,
                'paid_at' => null,
                'subtotal' => 50000,
                'tax_total' => 10000,
                'total' => 60000,
                'notes' => 'Point-of-sale system support retainer, June.',
                'contact' => [
                    'phone' => '01142 738455',
                    'email' => 'finance@thistleoakretail.co.uk',
                ],
                'address' => [
                    'address_line_one' => '2 Fargate',
                    'city' => 'Sheffield',
                    'postcode' => 'S1 2HE',
                    'country' => 'United Kingdom',
                    'is_primary' => true,
                ],
            ],
            [
                'invoice_number' => 'INV-000005',
                'company_slug' => 'kestrel-build-contractors-ltd',
                'status_title' => 'Pending',
                'issue_date' => '2026-06-01',
                'due_date' => '2026-07-01',
                'sent_at' => '2026-06-01 08:50:00',
                'paid_at' => null,
                'subtotal' => 320000,
                'tax_total' => 64000,
                'total' => 384000,
                'notes' => 'Site management software licensing, Q2.',
                'contact' => [
                    'phone' => '01914 682037',
                    'email' => 'accounts@kestrelbuild.co.uk',
                ],
                'address' => [
                    'address_line_one' => '9 Grainger Street',
                    'city' => 'Newcastle upon Tyne',
                    'postcode' => 'NE1 5JQ',
                    'country' => 'United Kingdom',
                    'is_primary' => true,
                ],
            ],
            [
                'invoice_number' => 'INV-000006',
                'company_slug' => 'harrogate-data-centres-ltd',
                'status_title' => 'Cancelled',
                'issue_date' => '2026-03-10',
                'due_date' => '2026-04-10',
                'sent_at' => '2026-03-10 13:05:00',
                'paid_at' => null,
                'subtotal' => 210000,
                'tax_total' => 42000,
                'total' => 252000,
                'notes' => 'Colocation billing integration, cancelled by client.',
                'contact' => [
                    'phone' => '01423 561974',
                    'email' => 'billing@harrogatedatacentres.co.uk',
                ],
                'address' => [
                    'address_line_one' => '5 Cheltenham Parade',
                    'city' => 'Harrogate',
                    'postcode' => 'HG1 1DB',
                    'country' => 'United Kingdom',
                    'is_primary' => true,
                ],
            ],
        ];

        foreach ($invoices as $data) {
            $company = $companies->get($data['company_slug']);

            if (! $company) {
                continue;
            }

            $invoice = Invoice::updateOrCreate(
                ['invoice_number' => $data['invoice_number']],
                [
                    'company_id' => $company->id,
                    'order_id' => null,
                    'status_id' => $statuses->get($data['status_title'])?->id,
                    'issue_date' => $data['issue_date'],
                    'due_date' => $data['due_date'],
                    'sent_at' => $data['sent_at'],
                    'paid_at' => $data['paid_at'],
                    'subtotal' => $data['subtotal'],
                    'tax_total' => $data['tax_total'],
                    'total' => $data['total'],
                    'currency' => 'GBP',
                    'notes' => $data['notes'],
                    'created_by' => $creator->id,
                ]
            );

            if ($invoice->contact === null) {
                $invoice->contact()->create([
                    ...$data['contact'],
                    'created_by' => $creator->id,
                ]);
            }

            if ($invoice->address === null) {
                $invoice->address()->create([
                    ...$data['address'],
                    'created_by' => $creator->id,
                ]);
            }
        }
    }
}
