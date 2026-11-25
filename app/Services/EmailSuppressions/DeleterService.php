<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Actions\DeleteResource;
use App\Models\EmailSuppression;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete an email suppression.
     *
     * Deleting a suppression re-subscribes the address, so the action is
     * always audit logged with the actor (ISO 27001 A.8.15).
     *
     * @throws AuthorizationException When the email suppression is already deleted.
     * @throws \Exception
     */
    public function delete(
        EmailSuppression $emailSuppression,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $this->ensureDeletable($emailSuppression);

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $emailSuppression,
            function (EmailSuppression $emailSuppression) use ($actor, $deletedBy): void {
                $emailSuppression->deleted_by = $deletedBy;
                $emailSuppression->deleted_at = now();
                $emailSuppression->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_EMAIL_SUPPRESSION,
                    $actor,
                    $emailSuppression,
                    ['before' => $this->auditLogService->snapshot($emailSuppression)],
                );
            });
    }

    /**
     * Force delete an email suppression (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        EmailSuppression $emailSuppression,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $emailSuppression,
            function (EmailSuppression $emailSuppression) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_EMAIL_SUPPRESSION,
                    $actor,
                    $emailSuppression,
                    ['before' => $this->auditLogService->snapshot($emailSuppression)],
                );
            });
    }

    /**
     * Delete multiple email suppressions.
     *
     * Rows that are already deleted are skipped rather than rejected, so one
     * protected row cannot roll back the whole batch. The returned count
     * excludes them.
     *
     * @param  array<int, int>  $emailSuppressionIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $emailSuppressionIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($emailSuppressionIds, $deletedBy, &$count): void {
            $actor = User::findOrFail($deletedBy);

            $emailSuppressions = EmailSuppression::whereIn('id', $emailSuppressionIds)->get();

            foreach ($emailSuppressions as $emailSuppression) {
                if (! $this->activeChecker->canBeDeleted($emailSuppression)) {
                    continue;
                }

                if ($this->delete($emailSuppression, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    /**
     * Refuse to delete an email suppression that is already deleted.
     *
     * @throws AuthorizationException
     */
    protected function ensureDeletable(EmailSuppression $emailSuppression): void
    {
        if (! $this->activeChecker->canBeDeleted($emailSuppression)) {
            throw new AuthorizationException(
                'This email suppression has already been deleted.'
            );
        }
    }
}
