<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Answers\ImportAnswerRequest;
use App\Http\Requests\Answers\StoreAnswerRequest;
use App\Http\Requests\Answers\UpdateAnswerRequest;
use App\Http\Requests\BulkIdsRequest;
use App\Models\Answer;
use App\Services\Answers\ManagementService;
use App\Services\Answers\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnswerController extends Controller
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
     * Display a paginated, filterable list of answers.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Answer::class);

        $filters = $this->validatedFilters($request);

        return Inertia::render('Answers/Index', [
            ...$this->query->getPaginated($request->user(), $filters),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new answer.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Answer::class);

        $request->validate(['question_id' => ['nullable', 'integer', 'min:1']]);

        return Inertia::render('Answers/Create', [
            ...$this->query->getFormData(),
            'default_question_id' => $request->integer('question_id') ?: null,
            'permissions_meta' => [
                'can_view_correct' => $request->user()->can('viewCorrect', Answer::class),
            ],
        ]);
    }

    /**
     * Store a newly created answer.
     *
     * Validation and authorisation are handled upstream by
     * StoreAnswerRequest.
     */
    public function store(StoreAnswerRequest $request): JsonResponse|RedirectResponse
    {
        $answer = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($this->query->format($request->user(), $answer), 201);
        }

        return to_route('answers.show', $answer)
            ->with('success', 'Answer created.');
    }

    /**
     * Display the specified answer.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(Answer $answer, Request $request): Response
    {
        $this->authorize('view', $answer);

        return Inertia::render('Answers/Show', $this->query->getById($request->user(), $answer));
    }

    /**
     * Show the form for editing the specified answer.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(Answer $answer, Request $request): Response
    {
        $this->authorize('update', $answer);

        return Inertia::render('Answers/Edit', $this->query->getById($request->user(), $answer));
    }

    /**
     * Update the specified answer.
     *
     * Validation and authorisation are handled upstream by
     * UpdateAnswerRequest.
     */
    public function update(
        UpdateAnswerRequest $request,
        Answer $answer
    ): JsonResponse|RedirectResponse {
        $answer = $this->management->update($request, $answer);

        if ($request->wantsJson()) {
            return response()->json($this->query->format($request->user(), $answer));
        }

        return to_route('answers.show', $answer)
            ->with('success', 'Answer updated.');
    }

    /**
     * Soft delete the specified answer.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(Request $request, Answer $answer): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $answer);

        $this->management->destroy($answer, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return to_route('answers.index')
            ->with('success', 'Answer deleted.');
    }

    /**
     * Restore a soft-deleted answer.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     */
    public function restore(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $answer = Answer::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $answer);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return to_route('answers.show', $id)
            ->with('success', 'Answer restored.');
    }

    /**
     * Permanently delete an answer.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $answer = Answer::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $answer);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return to_route('answers.index')
            ->with('success', 'Answer permanently deleted.');
    }

    /**
     * Bulk soft-delete multiple answers.
     *
     * Authorises each answer individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', Answer::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (Answer $answer) => $this->authorize('delete', $answer)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', count($result['deleted']).' answer(s) deleted.');
    }

    /**
     * Bulk restore multiple soft-deleted answers.
     *
     * Authorises each answer individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', Answer::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (Answer $answer) => $this->authorize('restore', $answer)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', count($result['restored']).' answer(s) restored.');
    }

    /**
     * Import answers from an uploaded CSV file.
     *
     * Authorisation is handled by ImportAnswerRequest::authorize().
     */
    public function import(ImportAnswerRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()
            ->with('success', "{$result['imported']} answer(s) imported.")
            ->with('import_skipped', $result['skipped']);
    }

    /**
     * Export answers matching the current filters as a CSV download.
     *
     * The export contains the answer key, so the user needs both the
     * 'export' and 'viewCorrect' abilities. Users who cannot restore
     * answers never get trashed records in the export.
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Answer::class);
        $this->authorize('viewCorrect', Answer::class);

        $filters = $this->validatedFilters($request);

        if (! $request->user()->can('bulkRestore', Answer::class)) {
            unset($filters['trashed']);
        }

        return $this->management->export($filters);
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportAnswerRequest::authorize().
     */
    public function importPreview(ImportAnswerRequest $request): JsonResponse
    {
        $importer = $this->management->importer();
        $token = $importer->storeUpload($request->file('file'), $request->user()->id);

        return response()->json(['token' => $token, ...$importer->preview($token, $request->user()->id)]);
    }

    /**
     * Commit a previously previewed import, persisting valid rows only.
     *
     * Authorises via the 'import' and 'viewCorrect' abilities.
     */
    public function importCommit(Request $request): JsonResponse
    {
        $this->authorize('import', Answer::class);
        $this->authorize('viewCorrect', Answer::class);

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
            'question_id' => ['nullable', 'integer', 'min:1'],
            'is_correct' => ['nullable', 'boolean'],
            'trashed' => ['nullable', Rule::in(['with', 'only'])],
            'sort_by' => ['nullable', Rule::in(['position', 'answer', 'created_at', 'updated_at'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50, 100])],
        ]);
    }
}
