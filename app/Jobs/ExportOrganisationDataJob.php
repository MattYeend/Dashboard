<?php

namespace App\Jobs;

use App\Models\Organisation;
use App\Models\User;
use App\Notifications\OrganisationDataExportReady;
use App\Services\Organisations\DataExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportOrganisationDataJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly Organisation $organisation,
        private readonly User $requestedBy,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DataExportService $dataExportService): void
    {
        $export = $dataExportService->export($this->organisation, $this->requestedBy);

        $this->requestedBy->notify(new OrganisationDataExportReady($export));
    }
}
