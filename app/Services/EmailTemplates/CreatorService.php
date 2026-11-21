<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use App\Actions\CreateResource;
use App\Models\EmailTemplate;
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
     * Create a new email template.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): EmailTemplate
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): EmailTemplate {
                $emailTemplateData = $this->dataPreparation->prepareForCreation($data);

                $newEmailTemplate = EmailTemplate::create($emailTemplateData);

                $newEmailTemplate->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_EMAIL_TEMPLATE,
                    $actor,
                    $newEmailTemplate,
                    ['after' => $this->auditLogService->snapshot($newEmailTemplate)],
                );

                return $newEmailTemplate;
            });
    }
}