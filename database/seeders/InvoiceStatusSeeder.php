<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\InvoiceStatus;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Database\Seeders\Concerns\SeedsSystemStatuses;
use Illuminate\Database\Seeder;

class InvoiceStatusSeeder extends Seeder
{
    use ResolvesDefaultOrganisation,
        SeedsSystemStatuses;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organisation = $this->defaultOrganisation();

        $statuses = [
            [
                'system_key' => InvoiceStatus::KEY_DRAFT,
                'title' => 'Draft',
                'description' => 'Invoice created but not yet sent to the client.',
                'background_colour' => '#e5e7eb',
                'text_colour' => '#111827',
            ],
            [
                'title' => 'Pending',
                'description' => 'Invoice sent and awaiting payment.',
                'background_colour' => '#fef3c7',
                'text_colour' => '#92400e',
            ],
            [
                'system_key' => InvoiceStatus::KEY_SENT,
                'title' => 'Sent',
                'description' => 'Invoice has been delivered to the client.',
                'background_colour' => '#dbeafe',
                'text_colour' => '#1e40af',
            ],
            [
                'system_key' => InvoiceStatus::KEY_PARTIALLY_PAID,
                'title' => 'Partially Paid',
                'description' => 'Some, but not all, of the invoice has been paid.',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ],
            [
                'system_key' => InvoiceStatus::KEY_PAID,
                'title' => 'Paid',
                'description' => 'Invoice has been paid in full.',
                'background_colour' => '#d1fae5',
                'text_colour' => '#065f46',
            ],
            [
                'system_key' => InvoiceStatus::KEY_OVERDUE,
                'title' => 'Overdue',
                'description' => 'Invoice payment is past the due date.',
                'background_colour' => '#fee2e2',
                'text_colour' => '#991b1b',
            ],
            [
                'system_key' => InvoiceStatus::KEY_CANCELLED,
                'title' => 'Cancelled',
                'description' => 'Invoice has been cancelled and is no longer payable.',
                'background_colour' => '#f3f4f6',
                'text_colour' => '#374151',
            ],
            [
                'title' => 'Refunded',
                'description' => 'Payment has been refunded to the client.',
                'background_colour' => '#ede9fe',
                'text_colour' => '#5b21b6',
            ],
            [
                'title' => 'Void',
                'description' => 'Invoice has been voided and holds no financial value.',
                'background_colour' => '#f1f5f9',
                'text_colour' => '#475569',
            ],
        ];

        $this->seedSystemStatuses(InvoiceStatus::class, (int) $organisation->id, $statuses);
    }
}
