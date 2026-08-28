<?php

namespace App\Services\Attachments;

use App\Http\Requests\Attachments\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer,
        protected readonly AttachableTypeRegistryService $registry,
    ) {}

    /**
     * Store a newly uploaded attachment.
     */
    public function store(StoreAttachmentRequest $request): Attachment
    {
        $validated = $request->validated();

        return $this->creator->create(
            $validated['attachable_type'],
            $validated['attachable_id'],
            $request->file('file'),
            $request->user()->id,
        );
    }

    /**
     * Soft delete an attachment.
     */
    public function destroy(Attachment $attachment, User $actor): void
    {
        $this->destructor->delete($attachment, $actor->id);
    }

    /**
     * Restore a soft-deleted attachment.
     */
    public function restore(int $id, User $actor): Attachment
    {
        $attachment = Attachment::withTrashed()->findOrFail($id);

        return $this->restorer->restore($attachment, $actor->id);
    }

    /**
     * Force delete an attachment, permanently removing it and its
     * underlying file from the private disk.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $attachment = Attachment::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($attachment, $actor->id);
    }
}
