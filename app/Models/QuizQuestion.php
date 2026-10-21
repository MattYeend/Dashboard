<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Carbon\CarbonImmutable;
use Database\Factories\QuizQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $organisation_id
 * @property int $quiz_id
 * @property int $question_id
 * @property int $position
 * @property int|null $points
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read Quiz $quiz
 * @property-read Question $question
 */
#[Fillable([
    'quiz_id',
    'question_id',
    'position',
    'points',
])]
class QuizQuestion extends Pivot
{
    /** @use HasFactory<QuizQuestionFactory> */
    use BelongsToOrganisation, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quiz_question';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Get the quiz this link belongs to.
     *
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the question this link points to.
     *
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'points' => 'integer',
        ];
    }
}
