<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\Lessons\ImportLessonRequest;
use App\Http\Requests\Lessons\ReorderLessonsRequest;
use App\Http\Requests\Lessons\StoreLessonRequest;
use App\Http\Requests\Lessons\UpdateLessonRequest;
use App\Models\Lesson;
use App\Services\Lessons\ManagementService;
use App\Services\Lessons\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LessonController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        protected readonly ManagementService $management,
        protected readonly QueryService $query,
    ) {}

    /**
     * Display the lesson list.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Lesson::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'course_id',
                'is_preview',
                'status',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('Lessons/Index', $data);
    }

    /**
     * Show the create form, optionally pre-selecting a course.
     *
     * The pre-selected course is only honoured when it is one the user is
     * allowed to choose, so a query string cannot reveal other courses.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Lesson::class);

        $formData = $this->query->getFormData($request->user());

        $requestedCourseId = $request->integer('course_id');

        $selectedCourseId = collect($formData['course_options'])
            ->contains('id', $requestedCourseId)
            ? $requestedCourseId
            : null;

        return Inertia::render('Lessons/Create', [
            ...$formData,
            'selected_course_id' => $selectedCourseId,
        ]);
    }

    /**
     * Store a newly created lesson.
     *
     * Validation and authorisation are handled upstream by StoreLessonRequest.
     */
    public function store(StoreLessonRequest $request): JsonResponse|RedirectResponse
    {
        $lesson = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($lesson, 201);
        }

        return redirect()
            ->route('lessons.show', $lesson->id)
            ->with('success', 'Lesson created.');
    }

    /**
     * Display a lesson with its neighbours for navigation.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        Lesson $lesson,
        Request $request
    ): Response {
        $this->authorize('view', $lesson);

        $data = array_merge(
            $this->query->getById($request->user(), $lesson),
            [
                'navigation' => $this->query->neighbours($request->user(), $lesson),
                'canEdit' => $request->user()->can('update', $lesson),
            ],
        );

        return Inertia::render('Lessons/Show', $data);
    }

    /**
     * Show the form for editing an existing lesson.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Lesson $lesson,
        Request $request
    ): Response {
        $this->authorize('update', $lesson);

        $data = array_merge(
            $this->query->getById($request->user(), $lesson),
            $this->query->getFormData($request->user()),
        );

        return Inertia::render('Lessons/Edit', $data);
    }

    /**
     * Update the specified lesson.
     *
     * Validation and authorisation are handled upstream by UpdateLessonRequest.
     */
    public function update(
        UpdateLessonRequest $request,
        Lesson $lesson
    ): JsonResponse|RedirectResponse {
        $lesson = $this->management->update($request, $lesson);

        if ($request->wantsJson()) {
            return response()->json($lesson);
        }

        return redirect()
            ->route('lessons.show', $lesson->id)
            ->with('success', 'Lesson updated.');
    }

    /**
     * Soft delete a lesson.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request,
        Lesson $lesson
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $lesson);

        $this->management->destroy($lesson, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('lessons.index')
            ->with('success', 'Lesson deleted.');
    }

    /**
     * Restore a soft-deleted lesson.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $lesson = Lesson::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $lesson);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('lessons.index')
            ->with('success', 'Lesson restored.');
    }

    /**
     * Permanently delete a soft-deleted lesson.
     *
     * Only records that are already soft-deleted can be force deleted.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $lesson = Lesson::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $lesson);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('lessons.index')
            ->with('success', 'Lesson permanently deleted.');
    }

    /**
     * Bulk soft-delete multiple lessons.
     *
     * Authorises each lesson individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', Lesson::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (Lesson $lesson) => $this->authorize('delete', $lesson)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('lessons.index')
            ->with('success', count($result['deleted']).' lessons deleted.');
    }

    /**
     * Bulk restore multiple soft-deleted lessons.
     *
     * Authorises each lesson individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', Lesson::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (Lesson $lesson) => $this->authorize('restore', $lesson)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('lessons.index')
            ->with('success', count($result['restored']).' lessons restored.');
    }

    /**
     * Reorder the lessons in a course.
     *
     * Authorisation and the course lookup are handled by
     * ReorderLessonsRequest, which scopes the course to the organisation.
     */
    public function reorder(ReorderLessonsRequest $request): JsonResponse|RedirectResponse
    {
        $course = $request->course();

        abort_if($course === null, 404);

        $this->management->reorder(
            $course,
            $request->validated('ordered_ids'),
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Lessons reordered.']);
        }

        return redirect()
            ->route('courses.show', $course->id)
            ->with('success', 'Lessons reordered.');
    }

    /**
     * Import lessons from an uploaded CSV file into a course.
     *
     * Authorisation is handled by ImportLessonRequest::authorize().
     */
    public function import(ImportLessonRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('lessons.index')
            ->with('import_result', $result);
    }

    /**
     * Export lessons matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Lesson::class);

        return $this->management->export(
            $request->only(['search', 'course_id', 'is_preview', 'status', 'trashed']),
            $request->user()
        );
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportLessonRequest::authorize().
     */
    public function importPreview(ImportLessonRequest $request): JsonResponse
    {
        $importer = $this->management->importer();
        $token = $importer->storeUpload($request->file('file'), $request->user()->id);

        return response()->json(['token' => $token, ...$importer->preview($token, $request->user()->id)]);
    }

    /**
     * Commit a previously previewed import, persisting valid rows only.
     *
     * Authorises via the 'import' policy before proceeding.
     */
    public function importCommit(Request $request): JsonResponse
    {
        $this->authorize('import', Lesson::class);

        $request->validate(['token' => ['required', 'uuid']]);

        try {
            $result = $this->management->importer()->commit(
                $request->input('token'),
                $request->user()->id
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }
}
