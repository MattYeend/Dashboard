<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use App\Actions\RestoreResource;
use App\Models\EmailTemplate;
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
     * Restore a soft-deleted email template.
     *
     * @throws \Exception
     */
    public function restore(
        EmailTemplate $emailTemplate,
        int $restoredBy,
        ?User $actor = null,
    ): EmailTemplate {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $emailTemplate,
            function (EmailTemplate $emailTemplate) use ($actor, $restoredBy): void {
                $emailTemplate->restored_by = $restoredBy;
                $emailTemplate->restored_at = now();
                $emailTemplate->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_EMAIL_TEMPLATE,
                    $actor,
                    $emailTemplate,
                    ['before' => $this->auditLogService->snapshot($emailTemplate)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted email templates.
     *
     * @param  array<int, int>  $emailTemplateIds
     * @return int Number of email templates restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $emailTemplateIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($emailTemplateIds, $restoredBy, &$count): void {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, EmailTemplate> $emailTemplates */
            $emailTemplates = EmailTemplate::onlyTrashed()
                ->whereIn('id', $emailTemplateIds)
                ->get();

            foreach ($emailTemplates as $emailTemplate) {
                $this->restore($emailTemplate, $restoredBy, $actor);
                $count++;
            }
        });

        return $count;
    }
}