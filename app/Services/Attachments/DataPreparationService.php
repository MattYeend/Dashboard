<?php

namespace App\Services\Attachments;

class DataPreparationService
{
    /**
     * Inject the type registry so the create allow-list stays in sync
     * with the single source of truth used elsewhere.
     */
    public function __construct(
        private readonly AttachableTypeRegistryService $registry,
    ) {}

    /**
     * Prepare attachment data for creation.
     *
     * @param  array<string, mixed>  $fileMeta  Output of StoreUploadedFile::handle() - disk_path, original_filename, mime_type, size_bytes
     * @return array<string, mixed>
     */
    public function prepareForCreation(
        string $attachableType,
        int $attachableId,
        array $fileMeta,
    ): array {
        return [
            'attachable_type' => $this->resolveAttachableType($attachableType),
            'attachable_id' => $attachableId,
            'original_filename' => $fileMeta['original_filename'],
            'disk_path' => $fileMeta['disk_path'],
            'mime_type' => $fileMeta['mime_type'],
            'size_bytes' => $fileMeta['size_bytes'],
        ];
    }

    /**
     * Convert the short type key submitted by the form (e.g. "company")
     * into the fully-qualified class name stored in
     * attachments.attachable_type (e.g. "App\Models\Company"). Throws if
     * it isn't a recognised short key, in case a fully-qualified name is
     * ever passed through directly.
     */
    private function resolveAttachableType(string $attachableType): string
    {
        return $this->registry->modelClassForKey($attachableType)
            ?? throw new \InvalidArgumentException("Unrecognised attachable type: {$attachableType}");
    }
}
