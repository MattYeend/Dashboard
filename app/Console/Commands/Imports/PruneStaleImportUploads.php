<?php

namespace App\Console\Commands\Imports;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('imports:prune-stale-uploads')]
#[Description('Delete abandoned import CSV uploads older than one hour.')]
class PruneStaleImportUploads extends Command
{
    /**
     * Storage directories to sweep, one per importable module.
     *
     * @var array<int, string>
     */
    protected const DIRECTORIES = [
        'imports/users',
        'imports/contacts',
        'imports/task-statuses',
        'imports/tasks',
        'imports/order-statuses',
        'imports/orders',
        'imports/industries',
        'imports/companies',
        'imports/addresses',
        'imports/categories',
        'imports/posts',
        'imports/invoice-statuses',
        'imports/tags',
        'imports/invoices',
        'imports/invoice-items',
        'imports/pipeline-statuses',
        'imports/pipelines',
        'imports/pipeline-stages',
        'imports/deal-statuses',
        'imports/deals',
        'imports/ticket-priorities',
        'imports/ticket-statuses',
        'imports/tickets',
        'imports/labels',
        'imports/reports',
        'imports/comments',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deleted = 0;

        foreach (self::DIRECTORIES as $directory) {
            foreach (Storage::disk('local')->files($directory) as $file) {
                if (
                    Storage::disk('local')->lastModified($file)
                    < now()->subHour()->getTimestamp()
                ) {
                    Storage::disk('local')->delete($file);
                    $deleted++;
                }
            }
        }

        $this->info("Deleted {$deleted} stale import upload(s).");

        return self::SUCCESS;
    }
}
