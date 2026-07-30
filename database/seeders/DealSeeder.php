<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Deal;
use App\Models\DealStatus;
use App\Models\Invoice;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Seeder;

class DealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Deal::exists()) {
            $this->command->info('Deals already seeded, skipping...');

            return;
        }

        $companies = Company::whereIn('slug', [
            'brightwave-software-ltd',
            'thistle-oak-retail-group',
            'kestrel-build-contractors-ltd',
            'harrogate-data-centres-ltd',
        ])->get()->keyBy('slug');

        if ($companies->isEmpty()) {
            $this->command->warn('No companies found, skipping deal seeding...');

            return;
        }

        $pipeline = Pipeline::first();

        if (! $pipeline) {
            $this->command->warn('No pipelines found, skipping deal seeding...');

            return;
        }

        $stages = PipelineStage::where('pipeline_id', $pipeline->id)->get()->keyBy('title');

        if ($stages->isEmpty()) {
            $this->command->warn('No pipeline stages found, skipping deal seeding...');

            return;
        }

        $statuses = DealStatus::all()->keyBy('title');

        if ($statuses->isEmpty()) {
            $this->command->warn('No deal statuses found, skipping deal seeding...');

            return;
        }

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found, skipping deal seeding...');

            return;
        }

        $creator = $users->first();

        $invoices = Invoice::whereIn('invoice_number', [
            'INV-000001',
            'INV-000002',
        ])->get()->keyBy('invoice_number');

        $deals = [
            [
                'title' => 'Brightwave CRM Phase One Rollout',
                'description' => 'Bespoke CRM build for the sales and account management teams, covering pipeline tracking and reporting.',
                'company_slug' => 'brightwave-software-ltd',
                'stage_title' => 'Won',
                'status_title' => 'Won',
                'invoice_number' => 'INV-000001',
                'value' => 120000,
                'probability' => 100,
                'expected_close_date' => '2026-04-01',
                'closed_at' => '2026-04-01',
            ],
            [
                'title' => 'Brightwave CRM Phase Two Extension',
                'description' => 'Follow-on phase adding workflow automation and third-party integrations to the phase one CRM build.',
                'company_slug' => 'brightwave-software-ltd',
                'stage_title' => 'Negotiation',
                'status_title' => 'Open',
                'invoice_number' => 'INV-000002',
                'value' => 85000,
                'probability' => 70,
                'expected_close_date' => '2026-08-15',
                'closed_at' => null,
            ],
            [
                'title' => 'Thistle Oak POS Support Renewal',
                'description' => 'Annual renewal of the point-of-sale support retainer across all Thistle Oak retail sites.',
                'company_slug' => 'thistle-oak-retail-group',
                'stage_title' => 'Proposal Sent',
                'status_title' => 'Open',
                'invoice_number' => null,
                'value' => 60000,
                'probability' => 50,
                'expected_close_date' => '2026-09-01',
                'closed_at' => null,
            ],
            [
                'title' => 'Thistle Oak Self-Checkout Pilot',
                'description' => 'Pilot rollout of self-checkout kiosks in two Sheffield stores, with a view to a wider chain rollout.',
                'company_slug' => 'thistle-oak-retail-group',
                'stage_title' => 'Qualified',
                'status_title' => 'Open',
                'invoice_number' => null,
                'value' => 145000,
                'probability' => 30,
                'expected_close_date' => '2026-11-01',
                'closed_at' => null,
            ],
            [
                'title' => 'Kestrel Build Site Management Licensing',
                'description' => 'Annual licensing agreement for the site management platform across all active Kestrel construction sites.',
                'company_slug' => 'kestrel-build-contractors-ltd',
                'stage_title' => 'Negotiation',
                'status_title' => 'Open',
                'invoice_number' => null,
                'value' => 320000,
                'probability' => 80,
                'expected_close_date' => '2026-08-01',
                'closed_at' => null,
            ],
            [
                'title' => 'Kestrel Build Health & Safety Module',
                'description' => 'Add-on module for incident reporting and compliance tracking, bundled with the existing licensing agreement.',
                'company_slug' => 'kestrel-build-contractors-ltd',
                'stage_title' => 'Lead',
                'status_title' => 'Open',
                'invoice_number' => null,
                'value' => 42000,
                'probability' => 10,
                'expected_close_date' => '2026-12-01',
                'closed_at' => null,
            ],
            [
                'title' => 'Harrogate Data Centres Billing Integration',
                'description' => 'Colocation billing integration project, called off by the client before delivery began.',
                'company_slug' => 'harrogate-data-centres-ltd',
                'stage_title' => 'Lost',
                'status_title' => 'Lost',
                'invoice_number' => null,
                'value' => 210000,
                'probability' => 0,
                'expected_close_date' => '2026-04-10',
                'closed_at' => '2026-03-10',
            ],
            [
                'title' => 'Harrogate Data Centres Capacity Expansion',
                'description' => 'Early-stage discussions around additional rack capacity and monitoring tooling for the Harrogate facility.',
                'company_slug' => 'harrogate-data-centres-ltd',
                'stage_title' => 'Lead',
                'status_title' => 'Open',
                'invoice_number' => null,
                'value' => 95000,
                'probability' => 20,
                'expected_close_date' => '2026-10-15',
                'closed_at' => null,
            ],
        ];

        foreach ($deals as $data) {
            $company = $companies->get($data['company_slug']);
            $stage = $stages->get($data['stage_title']);

            if (! $company || ! $stage) {
                continue;
            }

            $invoice = $data['invoice_number'] ? $invoices->get($data['invoice_number']) : null;

            Deal::updateOrCreate(
                [
                    'title' => $data['title'],
                    'company_id' => $company->id,
                ],
                [
                    'description' => $data['description'],
                    'pipeline_id' => $pipeline->id,
                    'stage_id' => $stage->id,
                    'status_id' => $statuses->get($data['status_title'])?->id,
                    'invoice_id' => $invoice?->id,
                    'value' => $data['value'],
                    'currency' => 'GBP',
                    'probability' => $data['probability'],
                    'expected_close_date' => $data['expected_close_date'],
                    'closed_at' => $data['closed_at'],
                    'created_by' => $creator->id,
                ]
            );
        }
    }
}
