<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use App\Actions\DeleteResource;
use App\Models\EmailTemplate;
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
     * Soft delete an email template.
     *
     * @throws \Exception
     */
    public function delete(
        EmailTemplate $emailTemplate,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $emailTemplate,
            function (EmailTemplate $emailTemplate) use ($actor, $deletedBy): void {
                $emailTemplate->deleted_by = $deletedBy;
                $emailTemplate->deleted_at = now();
                $emailTemplate->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_EMAIL_TEMPLATE,
                    $actor,
                    $emailTemplate,
                    ['before' => $this->auditLogService->snapshot($emailTemplate)],
                );
            });
    }

    /**
     * Force delete an email template (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        EmailTemplate $emailTemplate,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $emailTemplate,
            function (EmailTemplate $emailTemplate) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_EMAIL_TEMPLATE,
                    $actor,
                    $emailTemplate,
                    ['before' => $this->auditLogService->snapshot($emailTemplate)],
                );
            });
    }

    /**
     * Delete multiple email templates.
     *
     * @param  array<int, int>  $emailTemplateIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $emailTemplateIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($emailTemplateIds, $deletedBy, &$count): void {
            $actor = User::findOrFail($deletedBy);

            $emailTemplates = EmailTemplate::whereIn('id', $emailTemplateIds)->get();

            foreach ($emailTemplates as $emailTemplate) {
                if ($this->delete($emailTemplate, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
