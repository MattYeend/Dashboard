<?php

namespace App\Services\Organisations;

use App\Models\Log;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Plans\SeatCalculatorService;

class MembershipSeatSyncService
{
    /**
     * Inject the required services into the membership seat sync service.
     */
    public function __construct(
        protected readonly SeatCalculatorService $seatCalculatorService,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Recalculate seats and update the organisation's active Stripe
     * subscription quantity, if any.
     */
    public function sync(Organisation $organisation, User $actor): void
    {
        $subscription = $organisation->subscriptions()->active()->first();

        if ($subscription === null) {
            return;
        }

        $before = $subscription->quantity;
        $seats = max($this->seatCalculatorService->currentSeatCount($organisation), 1);

        if ($seats === $before) {
            return;
        }

        $subscription->updateQuantity($seats);

        $this->auditLogService->record(
            Log::ACTION_UPDATE_SUBSCRIPTION_SEATS,
            $actor,
            $organisation,
            [
                'before' => ['seats' => $before],
                'after' => ['seats' => $seats],
            ],
        );
    }
}
