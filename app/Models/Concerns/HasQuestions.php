<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin Model
 */
trait HasQuestions
{
    /**
     * Get the questions that belong to this model, ordered by position.
     *
     * @return MorphMany<Question, $this>
     */
    public function questions(): MorphMany
    {
        return $this->morphMany(Question::class, 'questionable')->orderBy('position');
    }
}
