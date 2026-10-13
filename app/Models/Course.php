<?php
 
declare(strict_types=1);
 
namespace App\Models;
 
use App\Contracts\Auditable;
use App\Enums\CourseDifficulty;
use App\Traits\BelongsToOrganisation;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
 
/**
 * A learning course that groups lessons and quizzes.
 *
 * @property int $id
 * @property int $organisation_id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string|null $description
 * @property CourseDifficulty $difficulty
 * @property int|null $estimated_duration_minutes
 * @property int|null $instructor_id
 * @property Carbon|null $published_at
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $instructor
 */
#[Fillable([
    'title',
    'slug',
    'summary',
    'description',
    'difficulty',
    'estimated_duration_minutes',
    'instructor_id',
    'published_at',
    'meta',
    'created_by',
    'updated_by',
])]
class Course extends Model implements Auditable
{
    /** @use HasFactory<CourseFactory> */
    use BelongsToOrganisation;
    use HasFactory;
    use SoftDeletes;
 
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'difficulty' => CourseDifficulty::class,
            'published_at' => 'datetime',
            'restored_at' => 'datetime',
            'meta' => 'array',
        ];
    }
 
    /**
     * Get the instructor who leads this course.
     *
     * @return BelongsTo<User, $this>
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
 
    // /**
    //  * Get the lessons in this course.
    //  *
    //  * @return HasMany<Lesson, $this>
    //  */
    // public function lessons(): HasMany
    // {
    //     return $this->hasMany(Lesson::class)->orderBy('position');
    // }
 
    // /**
    //  * Get the quizzes in this course.
    //  *
    //  * @return HasMany<Quiz, $this>
    //  */
    // public function quizzes(): HasMany
    // {
    //     return $this->hasMany(Quiz::class);
    // }
 
    /**
     * Get the user who created this course.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
 
    /**
     * Get the user who last updated this course.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
 
    /**
     * Get the user who deleted this course.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
 
    /**
     * Get the user who restored this course.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }
 
    /**
     * Determine whether the course is visible to learners.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
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
            'title',
            'slug',
            'summary',
            'description',
            'difficulty',
            'estimated_duration_minutes',
            'instructor_id',
            'published_at',
        ]);
    }
}
