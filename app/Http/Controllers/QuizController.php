<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\Quizzes\ImportQuizRequest;
use App\Http\Requests\Quizzes\StoreQuizRequest;
use App\Http\Requests\Quizzes\UpdateQuizRequest;
use App\Models\Quiz;
use App\Services\Quizzes\FormatterService;
use App\Services\Quizzes\ManagementService;
use App\Services\Quizzes\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuizController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        protected readonly ManagementService $management,
        protected readonly QueryService $query,
        protected readonly FormatterService $formatter,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Quiz::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'course_id',
                'lesson_id',
                'status',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('Quizzes/Index', $data);
    }

    /**
     * Show the create form.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Quiz::class);

        return Inertia::render('Quizzes/Create', [
            'courses' => $this->query->courseOptions(),
            // 'questionOptions' => $this->query->questionOptions(),
            'selectedCourseId' => $request->integer('course_id') ?: null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreQuizRequest.
     */
    public function store(StoreQuizRequest $request): JsonResponse|RedirectResponse
    {
        $quiz = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($quiz, 201);
        }

        return redirect()
            ->route('quizzes.show', $quiz->id)
            ->with('success', 'Quiz created.');
    }

    /**
     * Display a quiz. Learners never receive correct answers.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(Request $request, Quiz $quiz): Response
    {
        $this->authorize('view', $quiz);

        $quiz->load([
            'course:id,title',
            'lesson:id,title',
            'questions.answers',
            'creator:id,name',
            'updater:id,name',
            'deleter:id,name',
            'restorer:id,name',
        ]);

        $canEdit = $request->user()->can('update', $quiz);

        return Inertia::render('Quizzes/Show', [
            'quiz' => $canEdit
                ? $this->formatter->format($quiz)
                : $this->formatter->formatForLearner($quiz),
            'canEdit' => $canEdit,
        ]);
    }

    /**
     * Show the edit form. Only editors can reach it, so answers are only
     * sent to editors.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(Quiz $quiz): Response
    {
        $this->authorize('update', $quiz);

        $quiz->load([
            'course:id,title',
            'lesson:id,title',
            'questions.answers',
        ]);

        return Inertia::render('Quizzes/Edit', [
            'quiz' => $this->formatter->format($quiz),
            'courses' => $this->query->courseOptions(),
            'lessons' => $this->query->lessonOptionsFor($quiz->course_id),
            // 'questionOptions' => $this->query->questionOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation and authorisation are handled upstream by UpdateQuizRequest.
     */
    public function update(
        UpdateQuizRequest $request,
        Quiz $quiz
    ): JsonResponse|RedirectResponse {
        $quiz = $this->management->update($request, $quiz);

        if ($request->wantsJson()) {
            return response()->json($quiz);
        }

        return redirect()
            ->route('quizzes.show', $quiz->id)
            ->with('success', 'Quiz updated.');
    }

    /**
     * Soft delete a quiz.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request,
        Quiz $quiz
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $quiz);

        $this->management->destroy($quiz, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('quizzes.index')
            ->with('success', 'Quiz deleted.');
    }

    /**
     * Restore a soft-deleted quiz.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $quiz = Quiz::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $quiz);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('quizzes.index')
            ->with('success', 'Quiz restored.');
    }

    /**
     * Permanently delete a soft-deleted quiz.
     *
     * Only records that are already soft-deleted can be force deleted.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $quiz = Quiz::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $quiz);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('quizzes.index')
            ->with('success', 'Quiz permanently deleted.');
    }

    /**
     * Bulk soft-delete multiple quizzes.
     *
     * Authorises each quiz individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', Quiz::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (Quiz $quiz) => $this->authorize('delete', $quiz)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('quizzes.index')
            ->with('success', count($result['deleted']).' quizzes deleted.');
    }

    /**
     * Bulk restore multiple soft-deleted quizzes.
     *
     * Authorises each quiz individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', Quiz::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (Quiz $quiz) => $this->authorize('restore', $quiz)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('quizzes.index')
            ->with('success', count($result['restored']).' quizzes restored.');
    }

    /**
     * Import quizzes from an uploaded CSV file.
     *
     * Authorisation is handled by ImportQuizRequest::authorize().
     */
    public function import(ImportQuizRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('quizzes.index')
            ->with('import_result', $result);
    }

    /**
     * Export quizzes matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Quiz::class);

        return $this->management->export(
            $request->only(['search', 'course_id', 'lesson_id', 'status', 'trashed'])
        );
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportQuizRequest::authorize().
     */
    public function importPreview(ImportQuizRequest $request): JsonResponse
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
        $this->authorize('import', Quiz::class);

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

    /**
     * Return the lessons for a course, used by the dependent dropdown.
     *
     * Authorises via the 'create' policy, so only editors can list lessons.
     */
    public function courseLessons(Request $request): JsonResponse
    {
        $this->authorize('create', Quiz::class);

        $validated = $request->validate(['course_id' => ['required', 'integer']]);

        return response()->json($this->query->lessonOptionsFor($validated['course_id']));
    }
}
