<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Http\Requests\Quizzes\ImportQuizRequest;
use App\Http\Requests\Quizzes\StoreQuizRequest;
use App\Http\Requests\Quizzes\UpdateQuizRequest;
use App\Models\Quiz;
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
     * Create a new quiz.
     */
    public function store(StoreQuizRequest $request): Quiz
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing quiz.
     */
    public function update(UpdateQuizRequest $request, Quiz $quiz): Quiz
    {
        return $this->updater->update(
            $quiz,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a quiz.
     */
    public function destroy(Quiz $quiz, User $actor): void
    {
        $this->destructor->delete($quiz, $actor->id, $actor);
    }

    /**
     * Restore a soft-deleted quiz.
     */
    public function restore(int $id, User $actor): Quiz
    {
        $quiz = Quiz::withTrashed()->findOrFail($id);

        return $this->restorer->restore($quiz, $actor->id, $actor);
    }

    /**
     * Force delete a quiz, permanently removing it from the database.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $quiz = Quiz::withTrashed()->findOrFail($id);

        $this->destructor->forceDelete($quiz, $actor->id);
    }

    /**
     * Bulk restore quizzes, authorising each one.
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

        $quizzes = Quiz::onlyTrashed()->whereIn('id', $requestedIds)->get();

        $restored = [];

        foreach ($quizzes as $quiz) {
            /** @var Quiz $quiz */
            $authoriseCallback($quiz);
            $this->restorer->restore($quiz, $actor->id, $actor);
            $restored[] = $quiz->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds->diff($quizzes->pluck('id'))->values()->all(),
        ];
    }

    /**
     * Bulk soft delete quizzes, authorising each one.
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

        $quizzes = Quiz::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($quizzes as $quiz) {
            /** @var Quiz $quiz */
            $authoriseCallback($quiz);
            $this->destructor->delete($quiz, $actor->id, $actor);
            $deleted[] = $quiz->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds->diff($quizzes->pluck('id'))->values()->all(),
        ];
    }

    /**
     * Import quizzes from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(ImportQuizRequest $request): array
    {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export quizzes matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }

    /**
     * Expose the importer for controller-level preview/commit actions.
     */
    public function importer(): ImporterService
    {
        return $this->importer;
    }
}
