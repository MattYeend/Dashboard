<?php

namespace App\Services\Backups;

use App\Actions\Backups\CreateBackup;
use App\Actions\Backups\DeleteBackup;
use App\Actions\Backups\ExportBackup;
use App\Actions\Backups\ImportBackup;
use App\Actions\Backups\ListBackups;
use App\Actions\Backups\RestoreBackup;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupManagementService
{
    public function __construct(
        protected CreateBackup $createBackup,
        protected ListBackups $listBackups,
        protected DeleteBackup $deleteBackup,
        protected ImportBackup $importBackup,
        protected ExportBackup $exportBackup,
        protected RestoreBackup $restoreBackup,
        protected AuditLogService $auditLogService,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(): array
    {
        return $this->listBackups->handle();
    }

    public function create(User $actor, bool $onlyDb = false): void
    {
        $this->createBackup->handle($onlyDb);

        $this->auditLogService->record(
            actionId: Log::ACTION_CREATE_BACKUP,
            actor: $actor,
            data: ['only_db' => $onlyDb],
        );
    }

    public function delete(User $actor, string $disk, string $filename): void
    {
        $this->deleteBackup->handle($disk, $filename);

        $this->auditLogService->record(
            actionId: Log::ACTION_DELETE_BACKUP,
            actor: $actor,
            data: ['disk' => $disk, 'filename' => basename($filename)],
        );
    }

    public function restore(User $actor, string $disk, string $filename): void
    {
        $this->restoreBackup->handle($disk, $filename);

        $this->auditLogService->record(
            actionId: Log::ACTION_RESTORE_BACKUP,
            actor: $actor,
            data: ['disk' => $disk, 'filename' => basename($filename)],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function import(User $actor, UploadedFile $file): array
    {
        $filename = $this->importBackup->handle($file);

        $this->auditLogService->record(
            actionId: Log::ACTION_IMPORT_BACKUP,
            actor: $actor,
            data: ['filename' => $filename],
        );

        return ['filename' => $filename];
    }

    public function export(User $actor, string $disk, string $filename): StreamedResponse
    {
        $this->auditLogService->record(
            actionId: Log::ACTION_EXPORT_BACKUP,
            actor: $actor,
            data: ['disk' => $disk, 'filename' => basename($filename)],
        );

        return $this->exportBackup->handle($disk, $filename);
    }
}
