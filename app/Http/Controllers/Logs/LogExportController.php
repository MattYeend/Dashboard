<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logs\ExportLogRequest;
use App\Models\Log;
use App\Services\Logs\ManagementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogExportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly ManagementService $managementService
    ) {}

    /**
     * Export the audit trail as CSV.
     */
    public function export(ExportLogRequest $request): StreamedResponse
    {
        $this->authorize('export', Log::class);

        return $this->managementService->export($request->validated());
    }
}
