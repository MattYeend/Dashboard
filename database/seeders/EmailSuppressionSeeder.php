<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EmailSuppression;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;

/**
 * Seeds real email suppressions for the default organisation. No Faker data.
 * Safe to run repeatedly: rows are matched on 'email'.
 */
class EmailSuppressionSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    public function run(): void
    {
        $organisation = $this->defaultOrganisation();

        $rows = [
            [
                'email' => 'do-not-contact@example.org',
                'reason' => EmailSuppression::REASON_MANUAL,
                'notes' => 'Seed example. Replace with real suppression entries.',
            ],
        ];

        foreach ($rows as $row) {
            EmailSuppression::firstOrCreate(
                [
                    'email' => $row['email'],
                    'organisation_id' => $organisation->id,
                ],
                $row + ['suppressed_at' => now()],
            );
        }
    }
}