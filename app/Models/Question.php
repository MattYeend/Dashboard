<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Auditable;
use App\Enums\QuestionType;
use App\Traits\BelongsToOrganisation;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $organisation_id
 * @property string $questionable_type
 * @property int $questionable_id
 * @property string $question
 * @property QuestionType $type
 * @property string|null $explanation
 * @property int $points
 * @property int $position
 * @property bool $is_required
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Model|null $questionable
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'questionable_type',
    'questionable_id',
    'question',
    'type',
    'explanation',
    'points',
    'position',
    'is_required',
    'meta',
])]
class Question extends Model implements Auditable
{
    /**
     * @use HasFactory<QuestionFactory>
     */
    use BelongsToOrganisation,
        HasFactory,
        SoftDeletes;

    /**
     * Get the model (for example a course or lesson) this question
     * belongs to. Soft-deleted owners are included so existing
     * questions can still be displayed.
     *
     * @return MorphTo<Model, $this>
     */
    public function questionable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * Get the quizzes this question is used in.
     *
     * @return BelongsToMany<Quiz, $this>
     */
    public function quizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'quiz_question')
            ->withPivot(['position', 'points'])
            ->withTimestamps();
    }

    /**
     * Get the user who created this question.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this question.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this question.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this question.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the question's auditable attributes.
     *
     * Used by the audit log to capture before/after state on create,
     * update, delete and restore actions.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'questionable_type',
            'questionable_id',
            'question',
            'type',
            'explanation',
            'points',
            'position',
            'is_required',
            'meta',
        ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'points' => 'integer',
            'position' => 'integer',
            'is_required' => 'boolean',
            'meta' => 'array',
            'restored_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
        ];
    }
}
