<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentSeeder extends Seeder
{
    /**
     * Fixture files committed under database/seeders/fixtures/attachments/.
     */
    private const FIXTURES = [
        ['file' => 'sample-msa.pdf', 'mime' => 'application/pdf'],
        ['file' => 'sample-logo.png', 'mime' => 'image/png'],
    ];

    public function run(): void
    {
        if (Attachment::exists()) {
            $this->command->info('Attachments already seeded, skipping...');

            return;
        }

        $company = Company::first();

        if (! $company) {
            $this->command->warn('No companies found, skipping attachment seeding...');

            return;
        }

        foreach (self::FIXTURES as $fixture) {
            $sourcePath = database_path('seeders/fixtures/attachments/'.$fixture['file']);

            if (! is_file($sourcePath)) {
                $this->command->warn("Fixture file missing: {$fixture['file']}, skipping.");

                continue;
            }

            $storedName = Str::uuid()->toString().'.'.pathinfo($fixture['file'], PATHINFO_EXTENSION);
            Storage::disk(Attachment::DISK)->put($storedName, file_get_contents($sourcePath));

            Attachment::create([
                'attachable_type' => $company->getMorphClass(),
                'attachable_id' => $company->id,
                'original_filename' => $fixture['file'],
                'disk_path' => $storedName,
                'mime_type' => $fixture['mime'],
                'size_bytes' => filesize($sourcePath),
            ]);
        }
    }
}
