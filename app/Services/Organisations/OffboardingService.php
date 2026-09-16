<?php

namespace App\Services\Organisations;

use App\Actions\Organisations\CascadeSoftDeleteOrganisationScopedRecords;
use App\Models\Log;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Handles the GDPR-style offboarding workflow for an Organisation: the
 * initial deletion request, and the later, deliberately manual, hard-delete
 * step once the retention window has passed.
 */
class OffboardingService
{
    private const int RETENTION_DAYS = 30;

    /**
     * Inject the required services into the offboarding service.
     */
    public function __construct(
        protected readonly ManagementService $managementService,
        protected readonly CascadeSoftDeleteOrganisationScopedRecords $cascadeSoftDelete,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Soft-delete the organisation via ManagementService (so it is
     * stamped and audited the same way as a normal delete), cascade
     * soft-deletes to every scoped record, and cancel any active Stripe
     * subscription so nothing is left dangling.
     */
    public function requestDeletion(Organisation $organisation, User $requestedBy): void
    {
        DB::transaction(function () use ($organisation, $requestedBy): void {
            $before = $organisation->auditSnapshot();

            $this->cancelActiveSubscription($organisation);
            $this->managementService->destroy($organisation, $requestedBy);
            $this->cascadeSoftDelete->handle($organisation, $requestedBy);

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
     * it, once the retention window has elapsed. Never triggered by
     * requestDeletion() itself; only run via the scheduled command.
     */
    public function hardDelete(Organisation $organisation, User $actor): void
    {
        if ($organisation->deleted_at === null || $organisation->deleted_at->gt(now()->subDays(self::RETENTION_DAYS))) {
            throw new RuntimeException('Organisation is not yet eligible for hard deletion.');
        }

        DB::transaction(function () use ($organisation, $actor): void {
            $before = $organisation->auditSnapshot();

            $this->cascadeSoftDelete->hardHandle($organisation);
            $this->managementService->forceDelete($organisation->id, $actor);

            $this->auditLogService->record(
                Log::ACTION_HARD_DELETE_ORGANISATION,
                $actor,
                null,
                ['before' => $before, 'after' => null],
            );
        });
    }

    /**
     * Cancel any active Stripe subscription on the organisation as part
     * of offboarding, so nothing is left dangling.
     */
    private function cancelActiveSubscription(Organisation $organisation): void
    {
        $subscription = $organisation->subscriptions()->active()->first();

        $subscription?->cancelNow();
    }
}
