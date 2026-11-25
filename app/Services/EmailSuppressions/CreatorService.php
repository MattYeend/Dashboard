<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Actions\CreateResource;
use App\Models\EmailSuppression;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new email suppression.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): EmailSuppression
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): EmailSuppression {
                $emailSuppressionData = $this->dataPreparation->prepareForCreation($data);

                $newEmailSuppression = EmailSuppression::create($emailSuppressionData);

                $newEmailSuppression->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_EMAIL_SUPPRESSION,
                    $actor,
                    $newEmailSuppression,
                    ['after' => $this->auditLogService->snapshot($newEmailSuppression)],
                );

                return $newEmailSuppression;
            });
    }
}
