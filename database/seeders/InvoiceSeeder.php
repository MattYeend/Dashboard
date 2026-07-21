<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
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
                'subtotal' => 120000,
                'tax_total' => 24000,
                'total' => 144000,
                'notes' => 'Bespoke CRM development, phase one.',
            ],
            [
                'invoice_number' => 'INV-000002',
                'company_slug' => 'brightwave-software-ltd',
                'status_title' => 'Sent',
                'issue_date' => '2026-06-15',
                'due_date' => '2026-07-15',
                'subtotal' => 85000,
                'tax_total' => 17000,
                'total' => 102000,
                'notes' => 'Bespoke CRM development, phase two.',
            ],
            [
                'invoice_number' => 'INV-000003',
                'company_slug' => 'thistle-oak-retail-group',
                'status_title' => 'Overdue',
                'issue_date' => '2026-05-01',
                'due_date' => '2026-06-01',
                'subtotal' => 50000,
                'tax_total' => 10000,
                'total' => 60000,
                'notes' => 'Point-of-sale system support retainer, May.',
            ],
            [
                'invoice_number' => 'INV-000004',
                'company_slug' => 'thistle-oak-retail-group',
                'status_title' => 'Draft',
                'issue_date' => null,
                'due_date' => null,
                'subtotal' => 50000,
                'tax_total' => 10000,
                'total' => 60000,
                'notes' => 'Point-of-sale system support retainer, June.',
            ],
            [
                'invoice_number' => 'INV-000005',
                'company_slug' => 'kestrel-build-contractors-ltd',
                'status_title' => 'Pending',
                'issue_date' => '2026-06-01',
                'due_date' => '2026-07-01',
                'subtotal' => 320000,
                'tax_total' => 64000,
                'total' => 384000,
                'notes' => 'Site management software licensing, Q2.',
            ],
            [
                'invoice_number' => 'INV-000006',
                'company_slug' => 'harrogate-data-centres-ltd',
                'status_title' => 'Cancelled',
                'issue_date' => '2026-03-10',
                'due_date' => '2026-04-10',
                'subtotal' => 210000,
                'tax_total' => 42000,
                'total' => 252000,
                'notes' => 'Colocation billing integration, cancelled by client.',
            ],
        ];

        foreach ($invoices as $data) {
            $company = $companies->get($data['company_slug']);

            if (! $company) {
                continue;
            }

            $contact = Contact::where('contactable_id', $company->id)
                ->where('contactable_type', Company::class)
                ->first();

            Invoice::updateOrCreate(
                ['invoice_number' => $data['invoice_number']],
                [
                    'company_id' => $company->id,
                    'contact_id' => $contact?->id,
                    'order_id' => null,
                    'status_id' => $statuses->get($data['status_title'])?->id,
                    'issue_date' => $data['issue_date'],
                    'due_date' => $data['due_date'],
                    'subtotal' => $data['subtotal'],
                    'tax_total' => $data['tax_total'],
                    'total' => $data['total'],
                    'currency' => 'GBP',
                    'notes' => $data['notes'],
                    'created_by' => $creator->id,
                ]
            );
        }
    }
}
