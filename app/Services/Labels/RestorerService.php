<?php

namespace App\Services\Labels;

use App\Actions\RestoreResource;
use App\Models\Label;
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
     * Restore a soft-deleted label.
     *
     * @throws \Exception
     */
    public function restore(
        Label $label,
        int $restoredBy,
        ?User $actor = null
    ): Label {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $label,
            function (Label $label) use ($actor, $restoredBy): void {
                $label->restored_by = $restoredBy;
                $label->restored_at = now();
                $label->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_LABEL,
                    $actor,
                    $label,
                    ['before' => $this->auditLogService->snapshot($label)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted labels.
     *
     * @return int Number of labels restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(array $labelIds, int $restoredBy): int
    {
        $count = 0;

        DB::transaction(function () use ($labelIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, Label> $labels */
            $labels = Label::withTrashed()
                ->whereIn('id', $labelIds)
                ->get();

            foreach ($labels as $label) {
                if ($label->trashed()) {
                    $this->restore($label, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
