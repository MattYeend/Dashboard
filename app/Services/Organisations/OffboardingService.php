<?php

namespace App\Services\Organisations;

use App\Actions\Organisations\CascadeSoftDeleteOrganisationScopedRecords;
use App\Models\Log;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OffboardingService
{
    private const int RETENTION_DAYS = 30;

    /**
     * Inject the required services into the offboarding service.
     */
    public function __construct(
        protected readonly DeleterService $deleter,
        protected readonly CascadeSoftDeleteOrganisationScopedRecords $cascadeSoftDelete,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Soft-delete the organisation, cascade soft-deletes to every scoped
     * record, and cancel any active subscription.
     */
    public function requestDeletion(Organisation $organisation, User $requestedBy): void
    {
        DB::transaction(function () use ($organisation, $requestedBy): void {
            $before = $organisation->auditSnapshot();

            $this->cancelActiveSubscription($organisation);

            $this->deleter->delete(
                $organisation,
                $requestedBy->id,
                $requestedBy,
            );

            $this->cascadeSoftDelete->handle(
                $organisation,
                $requestedBy,
            );

            $this->auditLogService->record(
                Log::ACTION_REQUEST_ORGANISATION_DELETION,
                $requestedBy,
                $organisation,
                ['before' => $before, 'after' => null],
            );
        });
    }

    /**
     * Permanently hard-delete an organisation and every record scoped to
     * it, once the retention window has elapsed.
     */
    public function hardDelete(Organisation $organisation, User $actor): void
    {
        if (
            $organisation->deleted_at === null
            || $organisation->deleted_at->gt(now()->subDays(self::RETENTION_DAYS))
        ) {
            throw new RuntimeException(
                'Organisation is not yet eligible for hard deletion.'
            );
        }

        DB::transaction(function () use ($organisation, $actor): void {
            $before = $organisation->auditSnapshot();

            $this->cascadeSoftDelete->hardHandle($organisation);

            $this->deleter->forceDelete(
                $organisation,
                $actor->id,
            );

            $this->auditLogService->record(
                Log::ACTION_HARD_DELETE_ORGANISATION,
                $actor,
                null,
                ['before' => $before, 'after' => null],
            );
        });
    }

    /**
     * Cancel any active Stripe subscription on the organisation.
     */
    private function cancelActiveSubscription(Organisation $organisation): void
    {
        $subscription = $organisation->subscriptions()->active()->first();

        $subscription?->cancelNow();
    }
}
