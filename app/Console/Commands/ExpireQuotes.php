<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Organisation;
use App\Services\Quotes\TransitionService;
use Illuminate\Console\Command;

class ExpireQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotes:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire sent quotes whose valid until date has passed, for every organisation';

    /**
     * Execute the console command.
     */
    public function handle(TransitionService $transitions): int
    {
        $total = 0;

        Organisation::query()->each(function (Organisation $organisation) use ($transitions, &$total): void {
            $total += (int) $organisation->execute(fn (): int => $transitions->expireOverdue());
        });

        $this->info("Expired {$total} quote(s).");

        return self::SUCCESS;
    }
}
