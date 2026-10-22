<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Http\Requests\Answers\ImportAnswerRequest;
use App\Http\Requests\Answers\StoreAnswerRequest;
use App\Http\Requests\Answers\UpdateAnswerRequest;
use App\Models\Answer;
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
     * Create a new answer.
     *
     * Users who cannot view correct answers cannot set them either.
     */
    public function store(
        StoreAnswerRequest $request
    ): Answer {
        return $this->creator->create(
            $this->withoutKey($request->validated(), $request->user()),
            $request->user()->id
        );
    }

    /**
     * Update an existing answer.
     */
    public function update(
        UpdateAnswerRequest $request,
        Answer $answer
    ): Answer {
        return $this->updater->update(
            $answer,
            $this->withoutKey($request->validated(), $request->user()),
            $request->user()->id
        );
    }

    /**
     * Soft delete an answer.
     */
    public function destroy(
        Answer $answer,
        User $actor
    ): void {
        $this->destructor->delete($answer, $actor->id);
    }

    /**
     * Restore a soft-deleted answer.
     */
    public function restore(
        int $id,
        User $actor
    ): Answer {
        $answer = Answer::withTrashed()->findOrFail($id);

        return $this->restorer->restore($answer, $actor->id);
    }

    /**
     * Force delete an answer, permanently removing it from the database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $answer = Answer::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($answer, $actor->id);
    }

    /**
     * Bulk restore answers.
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

        $answers = Answer::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($answers as $answer) {
            /** @var Answer $answer */
            $authoriseCallback($answer);
            $this->restorer->restore($answer, $actor->id);
            $restored[] = $answer->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($answers->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete answers.
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

        $answers = Answer::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($answers as $answer) {
            /** @var Answer $answer */
            $authoriseCallback($answer);
            $this->destructor->delete($answer, $actor->id);
            $deleted[] = $answer->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($answers->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import answers from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportAnswerRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export answers matching the given filters as a CSV download.
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

    /**
     * Strip the answer key fields for users who may not view them.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function withoutKey(array $data, User $actor): array
    {
        if (! $actor->can('viewCorrect', Answer::class)) {
            unset($data['is_correct'], $data['feedback']);
        }

        return $data;
    }
}
