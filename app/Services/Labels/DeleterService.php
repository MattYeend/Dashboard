<?php

namespace App\Services\Labels;

use App\Actions\DeleteResource;
use App\Models\Label;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

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
     * Soft delete a label.
     *
     * @throws \Exception
     */
    public function delete(Label $label, int $deletedBy, ?User $actor = null): bool
    {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $label,
            function (Label $label) use ($actor, $deletedBy): void {
                $label->deleted_by = $deletedBy;
                $label->deleted_at = now();
                $label->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_LABEL,
                    $actor,
                    $label,
                    ['before' => $this->auditLogService->snapshot($label)],
                );
            });
    }

    /**
     * Force delete a label (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(Label $label, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $label,
            function (Label $label) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_LABEL,
                    $actor,
                    $label,
                    ['before' => $this->auditLogService->snapshot($label)],
                );
            });
    }

    /**
     * Delete multiple labels.
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $labelIds, int $deletedBy): int
    {
        $count = 0;

        DB::transaction(function () use ($labelIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $labels = Label::whereIn('id', $labelIds)->get();

            foreach ($labels as $label) {
                if ($this->delete($label, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
