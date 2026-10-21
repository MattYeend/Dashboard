<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Http\Requests\Questions\ImportQuestionRequest;
use App\Http\Requests\Questions\StoreQuestionRequest;
use App\Http\Requests\Questions\UpdateQuestionRequest;
use App\Models\Question;
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
     * Create a new question.
     */
    public function store(
        StoreQuestionRequest $request
    ): Question {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing question.
     */
    public function update(UpdateQuestionRequest $request, Question $question): Question
    {
        return $this->updater->update(
            $question,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a question.
     */
    public function destroy(Question $question, User $actor): void
    {
        $this->destructor->delete($question, $actor->id);
    }

    /**
     * Restore a soft-deleted question.
     */
    public function restore(int $id, User $actor): Question
    {
        $question = Question::withTrashed()->findOrFail($id);

        return $this->restorer->restore($question, $actor->id);
    }

    /**
     * Force delete a question, permanently removing it from the
     * database.
     *
     * @throws \DomainException When the question is used in a quiz
     */
    public function forceDelete(int $id, User $actor): void
    {
        $question = Question::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($question, $actor->id);
    }

    /**
     * Bulk restore questions.
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

        $questionsById = Question::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get()
            ->keyBy('id');

        $restored = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $question = $questionsById->get($id);

            if ($question === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($question);

            $this->restorer->restore($question, $actor->id);

            $restored[] = $question->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $skipped,
        ];
    }

    /**
     * Bulk soft delete questions.
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

        $questionsById = Question::whereIn('id', $requestedIds)->get()->keyBy('id');

        $deleted = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $question = $questionsById->get($id);

            if ($question === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($question);

            $this->destructor->delete($question, $actor->id);

            $deleted[] = $question->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $skipped,
        ];
    }

    /**
     * Import questions from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportQuestionRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export questions matching the given filters as a CSV download.
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
