<?php

namespace App\Services\Attachments;

use App\Actions\CreateResource;
use App\Actions\StoreUploadedFile;
use App\Models\Attachment;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly StoreUploadedFile $storeUploadedFile,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Store the uploaded file and create a new attachment record.
     *
     * The file is written to disk under a randomised name before the
     * database row is created, so a failed/rolled-back create never
     * leaves an Attachment row pointing at a file that doesn't exist -
     * but also never leaves a DB reference to a file we didn't
     * generate ourselves.
     *
     * @throws ModelNotFoundException
     */
    public function create(
        string $attachableType,
        int $attachableId,
        UploadedFile $file,
        int $createdBy,
    ): Attachment {
        $actor = User::findOrFail($createdBy);

        // Validates the real MIME type and generates a random stored
        // filename - never trusts the client-supplied name or extension.
        $fileMeta = $this->storeUploadedFile->handle($file, Attachment::DISK);

        return $this->createResource->handle(
            $fileMeta,
            function (array $fileMeta) use ($attachableType, $attachableId, $createdBy, $actor): Attachment {
                $attachmentData = $this->dataPreparation->prepareForCreation(
                    $attachableType,
                    $attachableId,
                    $fileMeta,
                );

                $newAttachment = Attachment::create($attachmentData);

                $newAttachment->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_ATTACHMENT,
                    $actor,
                    $newAttachment,
                    ['after' => $this->auditLogService->snapshot($newAttachment)],
                );

                return $newAttachment;
            });
    }
}
