<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use App\Models\User;

class QueryService
{
    /**
     * Inject the required services into the query service.
     */
    public function __construct(
        protected readonly SortingService $sortingService,
        protected readonly FormatterService $formatterService,
        protected readonly AttachableTypeRegistryService $registry,
    ) {}

    /**
     * Get formatted attachments for a given attachable model, for
     * embedding on a Company/Contact/Deal/Order Show page.
     *
     * Attachments don't have their own standalone index page - they're
     * always fetched scoped to a parent record, hence a dedicated
     * method here rather than a generic getPaginated().
     *
     * @return array<string, mixed>
     */
    public function getForAttachable(User $actor, string $attachableKey, int $attachableId): array
    {
        $modelClass = $this->registry->modelClassForKey($attachableKey);

        if ($modelClass === null) {
            throw new \InvalidArgumentException("Unrecognised attachable type: {$attachableKey}");
        }

        $attachments = Attachment::query()
            ->where('attachable_type', $modelClass)
            ->where('attachable_id', $attachableId)
            ->with(['creator', 'deleter'])
            ->tap(fn ($query) => $this->sortingService->applySorting($query))
            ->get();

        return [
            'attachments' => $attachments->map(fn (Attachment $a) => $this->formatterService->format($a))->all(),
            'permissions_meta' => $this->getPermissions($actor),
        ];
    }

    /**
     * Get user permissions for the authenticated user, scoped to what
     * the embedded attachment widget actually needs to render.
     *
     * @return array<string, mixed>
     */
    protected function getPermissions(User $actor): array
    {
        return [
            'can_create' => $actor->can('create', Attachment::class),
            'can_delete' => $actor->can('delete', [Attachment::class]),
        ];
    }
}
