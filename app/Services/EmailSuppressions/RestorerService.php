<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Actions\RestoreResource;
use App\Models\EmailSuppression;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
     * Restore a soft-deleted email suppression.
     *
     * @throws \Exception
     */
    public function restore(
        EmailSuppression $emailSuppression,
        int $restoredBy,
        ?User $actor = null,
    ): EmailSuppression {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $emailSuppression,
            function (EmailSuppression $emailSuppression) use ($actor, $restoredBy): void {
                $emailSuppression->restored_by = $restoredBy;
                $emailSuppression->restored_at = now();
                $emailSuppression->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_EMAIL_SUPPRESSION,
                    $actor,
                    $emailSuppression,
                    ['before' => $this->auditLogService->snapshot($emailSuppression)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted email suppressions.
     *
     * @param  array<int, int>  $emailSuppressionIds
     * @return int Number of email suppressions restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $emailSuppressionIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($emailSuppressionIds, $restoredBy, &$count): void {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, EmailSuppression> $emailSuppressions */
            $emailSuppressions = EmailSuppression::onlyTrashed()
                ->whereIn('id', $emailSuppressionIds)
                ->get();

            foreach ($emailSuppressions as $emailSuppression) {
                $this->restore($emailSuppression, $restoredBy, $actor);
                $count++;
            }
        });

        return $count;
    }
}