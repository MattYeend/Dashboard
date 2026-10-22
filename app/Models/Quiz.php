<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Auditable;
use App\Traits\BelongsToOrganisation;
use Carbon\CarbonInterface;
use Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A quiz attached to a course and optionally a lesson.
 *
 * @property int $id
 * @property int $course_id
 * @property int|null $lesson_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property int $pass_mark_percentage
 * @property int|null $time_limit_minutes
 * @property int|null $max_attempts
 * @property bool $shuffle_questions
 * @property bool $show_correct_answers
 * @property Carbon|null $published_at
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property CarbonInterface|null $restored_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property CarbonInterface|null $deleted_at
 * @property-read Course|null $course
 * @property-read Lesson|null $lesson
 */
#[Fillable([
    'course_id',
    'lesson_id',
    'title',
    'slug',
    'description',
    'pass_mark_percentage',
    'time_limit_minutes',
    'max_attempts',
    'shuffle_questions',
    'show_correct_answers',
    'published_at',
    'meta',
    'created_by',
    'updated_by',
])]
class Quiz extends Model implements Auditable
{
    /**
     * @use HasFactory<QuizFactory>
     */
    use BelongsToOrganisation,
        HasFactory,
        SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'pass_mark_percentage' => 'integer',
            'time_limit_minutes' => 'integer',
            'max_attempts' => 'integer',
            'shuffle_questions' => 'boolean',
            'show_correct_answers' => 'boolean',
            'published_at' => 'datetime',
            'restored_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * Get the course this quiz belongs to.
     *
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the lesson this quiz is attached to, if any.
     *
     * @return BelongsTo<Lesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the questions in this quiz, in order.
     *
     * @return BelongsToMany<Question, $this>
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'quiz_question')
            ->withPivot(['position', 'points'])
            ->withTimestamps()
            ->orderByPivot('position');
    }

    /**
     * Get the user who created this quiz.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this quiz.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this quiz.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this quiz.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Determine whether the quiz is visible to learners.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    /**
     * Limit the query to quizzes whose publish date has passed.
     *
     * @param  Builder<Quiz>  $query
     * @return Builder<Quiz>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Limit the query to published quizzes in published courses.
     *
     * @param  Builder<Quiz>  $query
     * @return Builder<Quiz>
     */
    public function scopeVisibleToLearners(Builder $query): Builder
    {
        return $query->published()->whereHas('course', function (Builder $course): void {
            $course->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        });
    }

    /**
     * Determine whether a learner may see this quiz (the course must be
     * loaded to avoid an extra query).
     */
    public function isVisibleToLearners(): bool
    {
        $course = $this->course;

        return $this->isPublished()
            && $course !== null
            && $course->published_at !== null
            && $course->published_at->isPast();
    }

    /**
     * Get the data captured in audit log snapshots.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'organisation_id',
            'course_id',
            'lesson_id',
            'title',
            'slug',
            'description',
            'pass_mark_percentage',
            'time_limit_minutes',
            'max_attempts',
            'shuffle_questions',
            'show_correct_answers',
            'published_at',
        ]) + [
            'questions' => $this->questions()->get()->map(fn (Question $question): array => [
                'question_id' => $question->id,
                'position' => $question->pivot->position,
                'points' => $question->pivot->points,
            ])->all(),
        ];
    }
}
