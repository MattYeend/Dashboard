<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use App\Http\Requests\EmailTemplates\ImportEmailTemplateRequest;
use App\Http\Requests\EmailTemplates\StoreEmailTemplateRequest;
use App\Http\Requests\EmailTemplates\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer,
        protected readonly ImporterService $importer,
        protected readonly ExporterService $exporter,
    ) {}

    /**
     * Create a new email template.
     */
    public function store(
        StoreEmailTemplateRequest $request
    ): EmailTemplate {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing email template.
     */
    public function update(
        UpdateEmailTemplateRequest $request,
        EmailTemplate $emailTemplate
    ): EmailTemplate {
        return $this->updater->update(
            $emailTemplate,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete an email template.
     */
    public function destroy(
        EmailTemplate $emailTemplate,
        User $actor
    ): void {
        $this->destructor->delete($emailTemplate, $actor->id, $actor);
    }

    /**
     * Restore a soft-deleted email template.
     */
    public function restore(
        int $id,
        User $actor
    ): EmailTemplate {
        $emailTemplate = EmailTemplate::withTrashed()->findOrFail($id);

        return $this->restorer->restore($emailTemplate, $actor->id, $actor);
    }

    /**
     * Force delete an email template, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $emailTemplate = EmailTemplate::withTrashed()->findOrFail($id);

        $this->destructor->forceDelete($emailTemplate, $actor->id);
    }

    /**
     * Bulk restore email templates.
     *
     * @param  array<int, int>  $ids
     * @return array{restored: array<int, int>, skipped: array<int, int>}
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $emailTemplates = EmailTemplate::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($emailTemplates as $emailTemplate) {
            /** @var EmailTemplate $emailTemplate */
            $authoriseCallback($emailTemplate);
            $this->restorer->restore($emailTemplate, $actor->id, $actor);
            $restored[] = $emailTemplate->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($emailTemplates->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete email templates.
     *
     * @param  array<int, int>  $ids
     * @return array{deleted: array<int, int>, skipped: array<int, int>}
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $emailTemplates = EmailTemplate::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($emailTemplates as $emailTemplate) {
            /** @var EmailTemplate $emailTemplate */
            $authoriseCallback($emailTemplate);
            $this->destructor->delete($emailTemplate, $actor->id, $actor);
            $deleted[] = $emailTemplate->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($emailTemplates->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import email templates from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportEmailTemplateRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export email templates matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }

    /**
     * Expose the importer service for controller-level import actions
     * (preview/commit) that operate outside the request-driven import().
     */
    public function importer(): ImporterService
    {
        return $this->importer;
    }
}