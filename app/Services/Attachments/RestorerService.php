<?php

namespace App\Services\Attachments;

use App\Actions\RestoreResource;
use App\Models\Attachment;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted attachment.
     *
     * The underlying file was never removed on soft delete, so
     * restoring the row is sufficient to make the attachment fully
     * available again.
     *
     * @throws \Exception
     */
    public function restore(Attachment $attachment, int $restoredBy): Attachment
    {
        $actor = User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $attachment,
            function (Attachment $attachment) use ($actor, $restoredBy): void {
                $attachment->restored_by = $restoredBy;
                $attachment->restored_at = now();
                $attachment->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_ATTACHMENT,
                    $actor,
                    $attachment,
                    ['before' => $this->auditLogService->snapshot($attachment)],
                );
            });
    }
}
