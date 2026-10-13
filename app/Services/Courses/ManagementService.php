<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Http\Requests\Courses\ImportCourseRequest;
use App\Http\Requests\Courses\StoreCourseRequest;
use App\Http\Requests\Courses\UpdateCourseRequest;
use App\Models\Course;
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
     * Create a new course.
     */
    public function store(StoreCourseRequest $request): Course
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing course.
     */
    public function update(UpdateCourseRequest $request, Course $course): Course
    {
        return $this->updater->update(
            $course,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a course.
     */
    public function destroy(Course $course, User $actor): void
    {
        $this->destructor->delete($course, $actor->id, $actor);
    }

    /**
     * Restore a soft-deleted course.
     */
    public function restore(int $id, User $actor): Course
    {
        $course = Course::withTrashed()->findOrFail($id);

        return $this->restorer->restore($course, $actor->id, $actor);
    }

    /**
     * Force delete a course, permanently removing it from the database.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $course = Course::withTrashed()->findOrFail($id);

        $this->destructor->forceDelete($course, $actor->id);
    }

    /**
     * Bulk restore courses.
     *
     * @param  array<int, int>  $ids
     * @return array{restored: array<int, int>, skipped: array<int, int>}
     */
    public function bulkRestore(array $ids, User $actor, callable $authoriseCallback): array
    {
        $requestedIds = collect($ids)->unique()->values();

        $courses = Course::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->orderBy('id')
            ->get();

        $restored = [];

        foreach ($courses as $course) {
            /** @var Course $course */
            $authoriseCallback($course);
            $this->restorer->restore($course, $actor->id, $actor);
            $restored[] = $course->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($courses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete courses.
     *
     * @param  array<int, int>  $ids
     * @return array{deleted: array<int, int>, skipped: array<int, int>}
     */
    public function bulkDelete(array $ids, User $actor, callable $authoriseCallback): array
    {
        $requestedIds = collect($ids)->unique()->values();

        $courses = Course::whereIn('id', $requestedIds)
            ->orderBy('id')
            ->get();

        $deleted = [];

        foreach ($courses as $course) {
            /** @var Course $course */
            $authoriseCallback($course);
            $this->destructor->delete($course, $actor->id, $actor);
            $deleted[] = $course->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($courses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import courses from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(ImportCourseRequest $request): array
    {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export courses matching the given filters as a CSV download.
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
