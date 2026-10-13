<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\Courses\StoreCourseRequest;
use App\Http\Requests\Courses\UpdateCourseRequest;
use App\Models\Course;
use App\Services\Courses\ManagementService;
use App\Services\Courses\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
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
     * Display a listing of the resource.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Course::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'difficulty',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('Courses/Index', $data);
    }

    /**
     * Show the form for creating a new course.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Course::class);

        return Inertia::render('Courses/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreCourseRequest.
     */
    public function store(StoreCourseRequest $request): JsonResponse|RedirectResponse
    {
        $course = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($course, 201);
        }

        return redirect()
            ->route('courses.show', $course->id)
            ->with('success', 'Course created.');
    }

    /**
     * Display the specified resource.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        Course $course,
        Request $request
    ): Response {
        $this->authorize('view', $course);

        $data = $this->query->getById(
            $request->user(),
            $course
        );

        return Inertia::render('Courses/Show', $data);
    }

    /**
     * Show the form for editing an existing course.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Course $course,
        Request $request
    ): Response {
        $this->authorize('update', $course);

        $data = array_merge(
            $this->query->getById(
                $request->user(),
                $course
            ),
            $this->query->getFormData(),
        );

        return Inertia::render('Courses/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation and authorisation are handled upstream by UpdateCourseRequest.
     */
    public function update(
        UpdateCourseRequest $request,
        Course $course
    ): JsonResponse|RedirectResponse {
        $course = $this->management->update($request, $course);

        if ($request->wantsJson()) {
            return response()->json($course);
        }

        return redirect()
            ->route('courses.show', $course->id)
            ->with('success', 'Course updated.');
    }

    /**
     * Soft delete a course.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request,
        Course $course
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $course);

        $this->management->destroy($course, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('courses.index')
            ->with('success', 'Course deleted.');
    }

    /**
     * Restore a soft-deleted course.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $course = Course::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $course);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('courses.index')
            ->with('success', 'Course restored.');
    }

    /**
     * Permanently delete a soft-deleted course.
     *
     * Only records that are already soft-deleted can be force deleted.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $course = Course::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $course);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('courses.index')
            ->with('success', 'Course permanently deleted.');
    }

    /**
     * Bulk soft-delete multiple courses.
     *
     * Authorises each course individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', Course::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (Course $course) => $this->authorize('delete', $course)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('courses.index')
            ->with('success', count($result['deleted']) . ' courses deleted.');
    }

    /**
     * Bulk restore multiple soft-deleted courses.
     *
     * Authorises each course individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', Course::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (Course $course) => $this->authorize('restore', $course)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('courses.index')
            ->with('success', count($result['restored']) . ' courses restored.');
    }
}
