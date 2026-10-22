<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;

class QueryService
{
    /**
     * Inject the required services into the query service.
     */
    public function __construct(
        protected readonly SortingService $sortingService,
        protected readonly TrashFilterService $trashFilterService,
        protected readonly FilterService $filterService,
        protected readonly FormatterService $formatterService
    ) {}

    /**
     * Get paginated answers with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(
        User $user,
        array $filters = []
    ): array {
        $canViewCorrect = $user->can('viewCorrect', Answer::class);

        if (! $canViewCorrect) {
            unset($filters['is_correct']);
        }

        if (! $user->can('bulkRestore', Answer::class)) {
            unset($filters['trashed']);
        }

        $query = $this->buildQuery($filters);
        $paginated = $this->paginate(
            $query,
            max(1, min((int) ($filters['per_page'] ?? 15), 100)),
            $canViewCorrect
        );

        return array_merge(
            $paginated,
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get a single answer by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, Answer $answer): array
    {
        $answer->loadMissing([
            'question',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        return array_merge(
            [
                'answer' => $this->formatterService->format(
                    $answer,
                    $user->can('viewCorrect', Answer::class)
                ),
                'abilities' => $this->getAbilities($user, $answer),
            ],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get data needed to populate create and edit forms.
     *
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        return [
            'question_options' => Question::query()
                ->latest('id')
                ->limit(200)
                ->get(['id', 'question'])
                ->map(fn (Question $question): array => [
                    'value' => (string) $question->id,
                    'label' => '#'.$question->id.' '.str($question->question)->limit(60)->toString(),
                ])
                ->all(),
        ];
    }

    /**
     * Format a single answer for a JSON response, respecting the answer key.
     *
     * @return array<string, mixed>
     */
    public function format(User $user, Answer $answer): array
    {
        $answer->loadMissing([
            'question',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        return $this->formatterService->format(
            $answer,
            $user->can('viewCorrect', Answer::class)
        );
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Answer>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Answer::query()->with([
            'question',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     *
     * @param  Builder<Answer>  $query
     * @return array<string, mixed>
     */
    protected function paginate(
        Builder $query,
        int $perPage,
        bool $canViewCorrect
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'answers' => [
                'data' => array_map(
                    fn (Answer $answer) => $this->formatterService->format($answer, $canViewCorrect),
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
     * Get list level permissions for the authenticated user.
     *
     * @return array<string, mixed>
     */
    protected function getPermissions(User $user): array
    {
        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', Answer::class),
                'can_view_any' => $user->can('viewAny', Answer::class),
                'can_export' => $user->can('export', Answer::class),
                'can_import' => $user->can('import', Answer::class),
                'can_delete' => $user->can('bulkDelete', Answer::class),
                'can_restore' => $user->can('bulkRestore', Answer::class),
                'can_view_correct' => $user->can('viewCorrect', Answer::class),
            ],
        ];
    }

    /**
     * Get record level abilities for the authenticated user.
     *
     * @return array<string, mixed>
     */
    protected function getAbilities(User $user, Answer $answer): array
    {
        return [
            'can_update' => $user->can('update', $answer),
            'can_delete' => $user->can('delete', $answer),
            'can_restore' => $user->can('restore', $answer),
            'can_force_delete' => $user->can('forceDelete', $answer),
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
     * Apply sorting and trash filtering to the query.
     *
     * @param  Builder<Answer>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Answer>
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
