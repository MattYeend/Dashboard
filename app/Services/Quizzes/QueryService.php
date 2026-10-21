<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
     * Get paginated quizzes with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(
        User $actor,
        array $filters = []
    ): array {
        $query = $this->buildQuery($actor, $filters);
        $paginated = $this->paginate(
            $query,
            $actor,
            max(1, min((int) ($filters['per_page'] ?? 15), 100))
        );

        return array_merge(
            $paginated,
            $this->getPermissions($actor),
            $this->baseData(),
        );
    }

    /**
     * Get a single quiz by ID, formatted for the actor's role.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, Quiz $quiz): array
    {
        $quiz->loadMissing([
            'course:id,title',
            'lesson:id,title',
            'questions.answers',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        $editor = $this->isEditor($user);

        $formatted = $editor
            ? $this->formatterService->format($quiz)
            : $this->formatterService->formatForLearner($quiz);

        return array_merge(
            ['quiz' => $formatted],
            $this->getPermissions($user),
            $this->baseData(),
            $editor ? ['question_options' => $this->questionOptions()] : [],
        );
    }

    /**
     * Get the data required to render the create-quiz form.
     *
     * @return array<string, mixed>
     */
    public function getCreateData(User $user): array
    {
        return array_merge(
            $this->getPermissions($user),
            $this->baseData(),
            ['question_options' => $this->questionOptions()],
        );
    }

    /**
     * Courses available in selects.
     *
     * @return Collection<int, Course>
     */
    public function courseOptions(): Collection
    {
        return Course::query()->select('id', 'title')->orderBy('title')->get();
    }

    /**
     * Lessons belonging to the given course.
     *
     * @return Collection<int, Lesson>
     */
    public function lessonOptionsFor(int $courseId): Collection
    {
        return Lesson::query()
            ->select('id', 'title')
            ->where('course_id', $courseId)
            ->orderBy('title')
            ->get();
    }

    /**
     * Questions that can be attached to a quiz.
     *
     * @return Collection<int, Question>
     */
    public function questionOptions(): Collection
    {
        return Question::query()->select('id', 'body', 'type')->orderBy('id')->get();
    }

    /**
     * Whether the user sees unpublished and trashed quizzes.
     */
    protected function isEditor(User $user): bool
    {
        return $user->can('edit quizzes');
    }

    /**
     * Build the base query with filters and visibility rules.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Quiz>
     */
    protected function buildQuery(User $actor, array $filters): Builder
    {
        $query = Quiz::query()
            ->with(['course:id,title', 'lesson:id,title', 'creator', 'updater', 'deleter', 'restorer'])
            ->withCount('questions');

        if ($this->isEditor($actor)) {
            $query = $this->trashFilterService->applyFilter($query, $filters['trashed'] ?? null);
        } else {
            $query = $this->restrictToPublished($query);
            $filters['status'] = null;
        }

        $query = $this->filterService->applyAll($query, $filters);

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'title',
            $filters['sort_direction'] ?? 'asc'
        );
    }

    /**
     * Non-editors only see published quizzes in published courses.
     *
     * @param  Builder<Quiz>  $query
     * @return Builder<Quiz>
     */
    protected function restrictToPublished(Builder $query): Builder
    {
        return $query->visibleToLearners();
    }

    /**
     * Paginate the query and return as plain arrays.
     *
     * @param  Builder<Quiz>  $query
     * @return array<string, mixed>
     */
    protected function paginate(
        Builder $query,
        User $actor,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();
        $editor = $this->isEditor($actor);

        return [
            'quizzes' => [
                'data' => array_map(
                    fn (Quiz $quiz) => $editor
                        ? $this->formatterService->format($quiz)
                        : $this->formatterService->formatForLearner($quiz),
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
     * Get permissions for the authenticated user.
     *
     * @return array<string, mixed>
     */
    protected function getPermissions(User $user): array
    {
        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', Quiz::class),
                'can_view_any' => $user->can('viewAny', Quiz::class),
                'can_export' => $user->can('export', Quiz::class),
            ],
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
            'statuses' => FilterService::STATUSES,
            'course_options' => $this->courseOptions(),
        ];
    }
}
