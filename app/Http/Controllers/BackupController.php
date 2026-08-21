<?php

namespace App\Http\Controllers;

use App\Http\Requests\Backups\CreateBackupRequest;
use App\Http\Requests\Backups\ImportBackupRequest;
use App\Services\Backups\BackupManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(protected BackupManagementService $backups) {}

    /**
     * Display a listing of available backups.
     */
    public function index(): Response
    {
        return Inertia::render('Backups/Index', [
            'backups' => $this->backups->list(),
            'permissions' => [
                'can_create' => Gate::allows('create backups'),
                'can_restore' => Gate::allows('restore backups'),
                'can_delete' => Gate::allows('delete backups'),
                'can_import' => Gate::allows('import backups'),
                'can_export' => Gate::allows('export backups'),
            ],
        ]);
    }

    /**
     * Trigger a new backup run.
     */
    public function store(CreateBackupRequest $request): RedirectResponse
    {
        $this->backups->create(Auth::user(), (bool) $request->validated('only_db', false));

        return back()->with('success', 'Backup started.');
    }

    /**
     * Import an uploaded zip as a backup.
     */
    public function import(ImportBackupRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->backups->import(Auth::user(), $request->file('file'));

        if ($request->wantsJson()) {
            return response()->json($result, 201);
        }

        return back()->with('success', 'Backup imported.');
    }

    /**
     * Download a backup.
     */
    public function export(string $filename): StreamedResponse
    {
        $disk = config('backup.backup.destination.disks')[0];

        return $this->backups->export(Auth::user(), $disk, $filename);
    }

    /**
     * Restore the database from a backup.
     */
    public function restore(string $filename): RedirectResponse
    {
        $disk = config('backup.backup.destination.disks')[0];

        $this->backups->restore(Auth::user(), $disk, $filename);

        return back()->with('success', 'Database restored from backup.');
    }

    /**
     * Delete a backup.
     */
    public function destroy(string $filename): RedirectResponse
    {
        $disk = config('backup.backup.destination.disks')[0];

        $this->backups->delete(Auth::user(), $disk, $filename);

        return redirect()->route('backups.index')->with('success', 'Backup deleted.');
    }
}
