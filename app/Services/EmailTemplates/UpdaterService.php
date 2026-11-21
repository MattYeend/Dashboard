<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use App\Actions\UpdateResource;
use App\Models\EmailTemplate;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing email template.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \DomainException When the email template is not editable.
     * @throws \Exception
     */
    public function update(
        EmailTemplate $emailTemplate,
        array $data,
        int $updatedBy
    ): EmailTemplate {
        $this->ensureEditable($emailTemplate);

        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($emailTemplate);

        $emailTemplateData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $emailTemplate,
            $emailTemplateData,
            function (EmailTemplate $emailTemplate) use ($actor, $before, $updatedBy): void {
                $emailTemplate->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $emailTemplate->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_EMAIL_TEMPLATE,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }

    /**
     * Refuse to edit an email template that is not editable.
     *
     * @throws \DomainException
     */
    protected function ensureEditable(EmailTemplate $emailTemplate): void
    {
        if (! $this->activeChecker->isEditable($emailTemplate)) {
            throw new \DomainException('Deleted email templates cannot be edited.');
        }
    }
}