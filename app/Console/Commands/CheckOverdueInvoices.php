<?php

namespace App\Console\Commands;

use App\Services\Invoices\ManagementService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('invoices:check-overdue')]
#[Description('Notify billing contacts of invoices that have become overdue')]
class CheckOverdueInvoices extends Command
{
    public function __construct(private readonly ManagementService $managementService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $notified = $this->managementService->notifyOverdue();

        $this->info("Notified for {$notified} overdue invoice(s).");

        return self::SUCCESS;
    }
}
