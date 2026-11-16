<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organisation;
use App\Models\PaymentStatus;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;

class PaymentStatusSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    /**
     * Run the database seeds.
     *
     * Keyed on system_key so the seeder can be re-run safely and so a
     * renamed title never produces a duplicate system status.
     */
    public function run(): void
    {
        $this->seedForOrganisation($this->defaultOrganisation());
    }

    /**
     * Seed the system payment statuses for one organisation.
     *
     * Bypasses the tenant global scope and sets organisation_id explicitly so
     * the result never depends on the current tenant context.
     */
    public function seedForOrganisation(Organisation $organisation): void
    {
        foreach ($this->getStatuses() as $status) {
            $exists = PaymentStatus::withoutGlobalScopes()
                ->withTrashed()
                ->where('organisation_id', $organisation->id)
                ->where('system_key', $status['system_key'])
                ->exists();

            if ($exists) {
                continue;
            }

            $model = new PaymentStatus($status);
            $model->organisation_id = $organisation->id; // Not mass-assignable by design.
            $model->save();
        }
    }

    /**
     * Get the predefined payment status records to seed.
     *
     * @return array<int, array<string, string|null>>
     */
    private function getStatuses(): array
    {
        return [
            [
                'system_key' => PaymentStatus::KEY_PENDING,
                'title' => 'Pending',
                'description' => 'Awaiting confirmation from the payment provider. Not yet counted against the invoice.',
                'background_colour' => '#fefcbf',
                'text_colour' => '#744210',
                'meta' => null,
            ],
            [
                'system_key' => PaymentStatus::KEY_COMPLETED,
                'title' => 'Completed',
                'description' => 'Funds received and confirmed. Counted against the invoice and immutable.',
                'background_colour' => '#c6f6d5',
                'text_colour' => '#22543d',
                'meta' => null,
            ],
            [
                'system_key' => PaymentStatus::KEY_FAILED,
                'title' => 'Failed',
                'description' => 'The payment did not go through. Never counted against the invoice.',
                'background_colour' => '#fed7d7',
                'text_colour' => '#742a2a',
                'meta' => null,
            ],
            [
                'system_key' => PaymentStatus::KEY_CANCELLED,
                'title' => 'Cancelled',
                'description' => 'A pending payment that was withdrawn before it completed.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
                'meta' => null,
            ],
        ];
    }
}
