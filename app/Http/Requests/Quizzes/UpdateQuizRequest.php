<?php

declare(strict_types=1);

namespace App\Http\Requests\Quizzes;

use App\Models\Quiz;

class UpdateQuizRequest extends StoreQuizRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        $quiz = $this->route('quiz');

        return $quiz instanceof Quiz
            && ($this->user()?->can('update', $quiz) ?? false);
    }
}
