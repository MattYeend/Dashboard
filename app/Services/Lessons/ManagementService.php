<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Http\Requests\Lessons\ImportLessonRequest;
use App\Http\Requests\Lessons\StoreLessonRequest;
use App\Http\Requests\Lessons\UpdateLessonRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $deleter,
        protected readonly RestorerService $restorer,
        protected readonly ImporterService $importer,
        protected readonly ExporterService $exporter,
        protected readonly PositionService $positionService,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Create a new lesson.
     */
    public function store(
        StoreLessonRequest $request
    ): Lesson {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing lesson.
     */
    public function update(UpdateLessonRequest $request, Lesson $lesson): Lesson
    {
        return $this->updater->update(
            $lesson,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a lesson.
     */
    public function destroy(Lesson $lesson, User $actor): void
    {
        $this->deleter->delete($lesson, $actor->id, $actor);
    }

    /**
     * Restore a soft-deleted lesson.
     */
    public function restore(int $id, User $actor): Lesson
    {
        $lesson = Lesson::withTrashed()->findOrFail($id);

        return $this->restorer->restore($lesson, $actor->id, $actor);
    }

    /**
     * Force delete a lesson, permanently removing it from the database.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $lesson = Lesson::withTrashed()->findOrFail($id);

        $this->deleter->forceDelete($lesson, $actor->id);
    }

    /**
     * Bulk restore lessons.
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

        $lessons = Lesson::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        DB::transaction(function () use ($lessons, $actor, $authoriseCallback, &$restored): void {
            foreach ($lessons as $lesson) {
                /** @var Lesson $lesson */
                $authoriseCallback($lesson);
                $this->restorer->restore($lesson, $actor->id, $actor);
                $restored[] = $lesson->id;
            }
        });

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($lessons->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete lessons.
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

        $lessons = Lesson::whereIn('id', $requestedIds)->get();

        $deleted = [];

        DB::transaction(function () use ($lessons, $actor, $authoriseCallback, &$deleted): void {
            foreach ($lessons as $lesson) {
                /** @var Lesson $lesson */
                $authoriseCallback($lesson);
                $this->deleter->delete($lesson, $actor->id, $actor);
                $deleted[] = $lesson->id;
            }
        });

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($lessons->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Reorder the lessons of a course.
     *
     * @param  array<int, int|string>  $orderedIds
     *
     * @throws ValidationException
     */
    public function reorder(Course $course, array $orderedIds, User $actor): void
    {
        $ids = collect($orderedIds)
            ->map(fn (int|string $id): int => (int) $id)
            ->unique()
            ->values();

        DB::transaction(function () use ($course, $ids, $actor): void {
            $activeIds = Lesson::query()
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->pluck('id');

            if ($ids->count() !== $activeIds->count() || $ids->diff($activeIds)->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'lesson_ids' => 'The order must contain every lesson in the course exactly once.',
                ]);
            }

            $this->positionService->reorder($course->id, $ids->all(), $actor->id);

            $this->auditLogService->record(
                Log::ACTION_REORDER_LESSON,
                $actor,
                $course,
                ['after' => ['lesson_ids' => $ids->all()]],
            );
        });
    }

    /**
     * Import lessons from an uploaded file into the chosen course.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportLessonRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id,
            $request->integer('course_id')
        );
    }

    /**
     * Export lessons matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters, User $actor): StreamedResponse
    {
        return $this->exporter->export($filters, $actor);
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
