<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\Questions\ImportQuestionRequest;
use App\Http\Requests\Questions\StoreQuestionRequest;
use App\Http\Requests\Questions\UpdateQuestionRequest;
use App\Models\Question;
use App\Services\Questions\ManagementService;
use App\Services\Questions\QueryService;
use DomainException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        private readonly QueryService $query,
        private readonly ManagementService $management,
    ) {}

    /**
     * Display a paginated, filterable list of questions.
     *
     * Passes paginated questions to the Questions/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Question::class);

        $filters = $this->validatedFilters($request);

        return Inertia::render('Questions/Index', [
            ...$this->query->getPaginated($request->user(), $filters),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new question.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Question::class);

        return Inertia::render('Questions/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created question.
     *
     * Validation and authorisation are handled upstream by
     * StoreQuestionRequest.
     */
    public function store(StoreQuestionRequest $request): JsonResponse|RedirectResponse
    {
        $question = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($question, 201);
        }

        return to_route('questions.show', $question)
            ->with('success', 'Question created.');
    }

    /**
     * Display the specified question.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(Question $question, Request $request): Response
    {
        $this->authorize('view', $question);

        $data = $this->query->getById($request->user(), $question);

        return Inertia::render('Questions/Show', $data);
    }

    /**
     * Show the form for editing the specified question.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(Question $question, Request $request): Response
    {
        $this->authorize('update', $question);

        $data = $this->query->getById($request->user(), $question);

        return Inertia::render('Questions/Edit', $data);
    }

    /**
     * Update the specified question.
     *
     * Validation and authorisation are handled upstream by
     * UpdateQuestionRequest.
     */
    public function update(
        UpdateQuestionRequest $request,
        Question $question
    ): JsonResponse|RedirectResponse {
        $question = $this->management->update($request, $question);

        if ($request->wantsJson()) {
            return response()->json($question);
        }

        return to_route('questions.show', $question)
            ->with('success', 'Question updated.');
    }

    /**
     * Soft delete the specified question.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(Request $request, Question $question): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $question);

        $this->management->destroy($question, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return to_route('questions.index')
            ->with('success', 'Question deleted.');
    }

    /**
     * Restore a soft-deleted question.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'restore' policy before proceeding.
     */
    public function restore(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $question = Question::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $question);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return to_route('questions.show', $id)
            ->with('success', 'Question restored.');
    }

    /**
     * Permanently delete a question.
     *
     * Authorises via the 'forceDelete' policy before proceeding. Domain
     * rule violations raised by the service are surfaced as an error.
     */
    public function forceDelete(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $question = Question::withTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $question);

        try {
            $this->management->forceDelete($id, $request->user());
        } catch (DomainException $exception) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->with('error', $exception->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return to_route('questions.index')
            ->with('success', 'Question permanently deleted.');
    }

    /**
     * Bulk soft-delete multiple questions.
     *
     * Authorises each question individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', Question::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (Question $question) => $this->authorize('delete', $question)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', count($result['deleted']).' question(s) deleted.');
    }

    /**
     * Bulk restore multiple soft-deleted questions.
     *
     * Authorises each question individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', Question::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (Question $question) => $this->authorize('restore', $question)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', count($result['restored']).' question(s) restored.');
    }

    /**
     * Import questions from an uploaded CSV file.
     *
     * Authorisation is handled by ImportQuestionRequest::authorize().
     */
    public function import(ImportQuestionRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()
            ->with('success', "{$result['imported']} question(s) imported.")
            ->with('import_skipped', $result['skipped']);
    }

    /**
     * Export questions matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding. Users who
     * cannot restore questions never get trashed records in the export.
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Question::class);

        $filters = $this->validatedFilters($request);

        if (! $request->user()->can('restore question')) {
            unset($filters['trashed']);
        }

        return $this->management->export($filters);
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportQuestionRequest::authorize().
     */
    public function importPreview(ImportQuestionRequest $request): JsonResponse
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
        $this->authorize('import', Question::class);

        $request->validate(['token' => ['required', 'uuid']]);

        return response()->json(
            $this->management->importer()->commit($request->input('token'), $request->user()->id)
        );
    }

    /**
     * Validate and return the list filters.
     *
     * @return array<string, mixed>
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::enum(QuestionType::class)],
            'questionable_type' => ['nullable', 'string', Rule::in(array_keys(config('questions.questionable_types', [])))],
            'trashed' => ['nullable', Rule::in(['with', 'only'])],
            'sort_by' => ['nullable', 'string', 'max:30'],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50, 100])],
        ]);
    }
}
