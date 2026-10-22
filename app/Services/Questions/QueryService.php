<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Services\Answers\FormatterService as AnswerFormatterService;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;

class QueryService
{
    /**
     * Inject the required services into the query service.
     */
    public function __construct(
        protected readonly AnswerFormatterService $answerFormatter,
        protected readonly SortingService $sortingService,
        protected readonly TrashFilterService $trashFilterService,
        protected readonly FilterService $filterService,
        protected readonly FormatterService $formatterService,
    ) {}

    /**
     * Get paginated questions with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(
        User $actor,
        array $filters = []
    ): array {
        if (! $actor->can('restore question')) {
            unset($filters['trashed']);
        }

        $query = $this->buildQuery($filters);
        $paginated = $this->paginate(
            $query,
            max(1, min((int) ($filters['per_page'] ?? 15), 100))
        );

        return array_merge(
            $paginated,
            $this->getPermissions($actor),
            $this->getFormData(),
            $this->baseData(),
        );
    }

    /**
     * Get a single question by ID.
     *
     * @return array<string, mixed>
     */
        /**
     * Get a single question by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, Question $question): array
    {
        $question->loadMissing([
            'questionable',
            'answers',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        $canViewCorrect = $user->can('viewCorrect', Answer::class);

        return array_merge(
            ['question' => $this->formatterService->format($question)],
            ['abilities' => $this->getAbilities($user, $question)],
            [
                'answers' => $question->answers
                    ->map(fn (Answer $answer): array => $this->answerFormatter->format($answer, $canViewCorrect))
                    ->values()
                    ->all(),
                'answer_permissions' => [
                    'can_create' => $user->can('create', Answer::class),
                    'can_view_correct' => $canViewCorrect,
                ],
            ],
            $this->getFormData(),
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get the data needed to render the Create/Edit/Index forms.
     *
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        return [
            'typeOptions' => array_map(
                fn (QuestionType $type): array => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ],
                QuestionType::cases(),
            ),
            'questionableTypes' => collect(array_keys(config('questions.questionable_types', [])))
                ->map(fn (string $alias): array => [
                    'value' => $alias,
                    'label' => ucfirst($alias),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Question>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Question::query()->with([
            'questionable',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as plain array.
     *
     * @param  Builder<Question>  $query
     * @return array<string, mixed>
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'questions' => [
                'data' => array_map(
                    fn (Question $question) => $this->formatterService->format($question),
                    $paginator->items()
                ),
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        ];
    }

    /**
     * Get collection level permissions for the authenticated user.
     *
     * @return array<string, mixed>
     */
    protected function getPermissions(User $user): array
    {
        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', Question::class),
                'can_view_any' => $user->can('viewAny', Question::class),
                'can_export' => $user->can('export', Question::class),
                'can_import' => $user->can('import', Question::class),
                'can_restore' => $user->can('restore question'),
            ],
        ];
    }

    /**
     * Get record level abilities for the authenticated user.
     *
     * @return array<string, bool>
     */
    protected function getAbilities(User $user, Question $question): array
    {
        return [
            'can_update' => $user->can('update', $question),
            'can_delete' => $user->can('delete', $question),
            'can_restore' => $user->can('restore', $question),
            'can_force_delete' => $user->can('forceDelete', $question),
        ];
    }

    /**
     * Get base data for the view.
     *
     * @return array<string, mixed>
     */
    protected function baseData(): array
    {
        return [
            'sort_fields' => $this->sortingService->getAvailableSortFields(),
            'trash_filters' => $this->trashFilterService->getFilterOptions(),
        ];
    }

    /**
     * Apply trash filtering and sorting to the query.
     *
     * @param  Builder<Question>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Question>
     */
    private function applySorting(
        Builder $query,
        array $filters
    ): Builder {
        $query = $this->trashFilterService->applyFilter(
            $query,
            $filters['trashed'] ?? null
        );

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'position',
            $filters['sort_direction'] ?? 'asc'
        );
    }
}
