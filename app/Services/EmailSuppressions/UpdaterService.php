<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Actions\UpdateResource;
use App\Models\EmailSuppression;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;

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
     * Update an existing email suppression.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException When the email suppression is deleted.
     * @throws \Exception
     */
    public function update(
        EmailSuppression $emailSuppression,
        array $data,
        int $updatedBy
    ): EmailSuppression {
        $this->ensureEditable($emailSuppression);

        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($emailSuppression);

        $emailSuppressionData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $emailSuppression,
            $emailSuppressionData,
            function (EmailSuppression $emailSuppression) use ($actor, $before, $updatedBy): void {
                $emailSuppression->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $emailSuppression->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_EMAIL_SUPPRESSION,
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
     * Refuse to edit an email suppression that has been deleted.
     *
     * @throws AuthorizationException
     */
    protected function ensureEditable(EmailSuppression $emailSuppression): void
    {
        if (! $this->activeChecker->isEditable($emailSuppression)) {
            throw new AuthorizationException(
                'A deleted email suppression cannot be edited. Restore it first.'
            );
        }
    }
}
