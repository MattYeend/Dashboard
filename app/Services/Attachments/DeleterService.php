<?php

namespace App\Services\Attachments;

use App\Actions\DeleteResource;
use App\Models\Attachment;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Storage;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete an attachment.
     *
     * The physical file is deliberately left on disk here - only a
     * force delete removes it - so that restore() can bring a
     * soft-deleted attachment back with its file intact.
     *
     * @throws \Exception
     */
    public function delete(Attachment $attachment, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $attachment,
            function (Attachment $attachment) use ($actor, $deletedBy): void {
                $attachment->deleted_by = $deletedBy;
                $attachment->deleted_at = now();
                $attachment->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_ATTACHMENT,
                    $actor,
                    $attachment,
                    ['before' => $this->auditLogService->snapshot($attachment)],
                );
            });
    }

    /**
     * Force delete an attachment (permanent deletion).
     *
     * Removes both the database row and the underlying file from the
     * private disk - the file is only ever removed at this point,
     * never on a plain soft delete.
     *
     * @throws \Exception
     */
    public function forceDelete(Attachment $attachment, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        // Captured before the closure runs, since the model/its
        // attributes may not be reliably readable after forceHandle()
        // has removed the row.
        $diskPath = $attachment->disk_path;

        return $this->deleteResource->forceHandle(
            $attachment,
            function (Attachment $attachment) use ($actor, $diskPath): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_ATTACHMENT,
                    $actor,
                    $attachment,
                    ['before' => $this->auditLogService->snapshot($attachment)],
                );

                Storage::disk(Attachment::DISK)->delete($diskPath);
            });
    }
}
