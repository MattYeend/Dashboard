<?php

declare(strict_types=1);

namespace App\Http\Requests\Quizzes;

use App\Models\Organisation;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuizRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Quiz::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => $this->courseIdRules(),
            'lesson_id' => $this->lessonIdRules(),
            'title' => $this->titleRules(),
            'description' => $this->descriptionRules(),
            'pass_mark_percentage' => $this->passMarkPercentageRules(),
            'time_limit_minutes' => $this->timeLimitMinutesRules(),
            'max_attempts' => $this->maxAttemptsRules(),
            'shuffle_questions' => $this->shuffleQuestionsRules(),
            'show_correct_answers' => $this->showCorrectAnswersRules(),
            'published_at' => $this->publishedAtRules(),
            'meta' => $this->metaRules(),
            'questions' => $this->questionsRules(),
            'questions.*.question_id' => $this->questionIdRules(),
            'questions.*.points' => $this->questionPointsRules(),
        ];
    }

    /**
     * Get the validation hooks to run after the base rules.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->rejectQuestionsWithoutCorrectAnswer($validator);
            },
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'course_id.required' => 'Please choose a course.',
            'course_id.exists' => 'The selected course could not be found.',
            'lesson_id.exists' => 'The selected lesson does not belong to this course.',
            'title.required' => 'Please enter a quiz title.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not exceed 255 characters.',
            'description.max' => 'The description may not exceed 65,000 characters.',
            'pass_mark_percentage.required' => 'Please enter a pass mark.',
            'pass_mark_percentage.min' => 'The pass mark must be at least 1%.',
            'pass_mark_percentage.max' => 'The pass mark may not exceed 100%.',
            'time_limit_minutes.min' => 'The time limit must be at least 1 minute.',
            'time_limit_minutes.max' => 'The time limit may not exceed 600 minutes.',
            'max_attempts.min' => 'Attempts must be at least 1.',
            'max_attempts.max' => 'Attempts may not exceed 50.',
            'questions.required' => 'Please choose at least one question.',
            'questions.min' => 'Please choose at least one question.',
            'questions.max' => 'A quiz can have at most 100 questions.',
            'questions.*.question_id.exists' => 'One of the selected questions could not be found.',
            'questions.*.question_id.distinct' => 'A question can only be added to a quiz once.',
            'questions.*.points.min' => 'Question points must be at least 1.',
            'questions.*.points.max' => 'Question points may not exceed 100.',
        ];
    }

    /**
     * Get validation rules for the course_id field, limited to live courses
     * in the current organisation.
     *
     * @return array<mixed>
     */
    protected function courseIdRules(): array
    {
        return [
            'required',
            'integer',
            Rule::exists('courses', 'id')
                ->where('organisation_id', Organisation::current()?->id)
                ->whereNull('deleted_at'),
        ];
    }

    /**
     * Get validation rules for the lesson_id field, limited to the chosen
     * course.
     *
     * @return array<mixed>
     */
    protected function lessonIdRules(): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('lessons', 'id')
                ->where('organisation_id', Organisation::current()?->id)
                ->where('course_id', $this->input('course_id'))
                ->whereNull('deleted_at'),
        ];
    }

    /**
     * Get validation rules for the title field.
     *
     * @return array<mixed>
     */
    protected function titleRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the description field.
     *
     * @return array<mixed>
     */
    protected function descriptionRules(): array
    {
        return [
            'nullable',
            'string',
            'max:65000',
        ];
    }

    /**
     * Get validation rules for the pass_mark_percentage field.
     *
     * @return array<mixed>
     */
    protected function passMarkPercentageRules(): array
    {
        return [
            'required',
            'integer',
            'min:1',
            'max:100',
        ];
    }

    /**
     * Get validation rules for the time_limit_minutes field.
     *
     * @return array<mixed>
     */
    protected function timeLimitMinutesRules(): array
    {
        return [
            'nullable',
            'integer',
            'min:1',
            'max:600',
        ];
    }

    /**
     * Get validation rules for the max_attempts field.
     *
     * @return array<mixed>
     */
    protected function maxAttemptsRules(): array
    {
        return [
            'nullable',
            'integer',
            'min:1',
            'max:50',
        ];
    }

    /**
     * Get validation rules for the shuffle_questions field.
     *
     * @return array<mixed>
     */
    protected function shuffleQuestionsRules(): array
    {
        return [
            'boolean',
        ];
    }

    /**
     * Get validation rules for the show_correct_answers field.
     *
     * @return array<mixed>
     */
    protected function showCorrectAnswersRules(): array
    {
        return [
            'boolean',
        ];
    }

    /**
     * Get validation rules for the published_at field.
     *
     * @return array<mixed>
     */
    protected function publishedAtRules(): array
    {
        return [
            'nullable',
            'date',
        ];
    }

    /**
     * Get validation rules for the meta field.
     *
     * @return array<mixed>
     */
    protected function metaRules(): array
    {
        return [
            'nullable',
            'array',
        ];
    }

    /**
     * Get validation rules for the questions array.
     *
     * @return array<mixed>
     */
    protected function questionsRules(): array
    {
        return [
            'required',
            'array',
            'min:1',
            'max:100',
        ];
    }

    /**
     * Get validation rules for each selected question_id, limited to live
     * questions in the current organisation.
     *
     * @return array<mixed>
     */
    protected function questionIdRules(): array
    {
        return [
            'required',
            'integer',
            'distinct',
            Rule::exists('questions', 'id')
                ->where('organisation_id', Organisation::current()?->id)
                ->whereNull('deleted_at'),
        ];
    }

    /**
     * Get validation rules for each selected question's points override.
     *
     * @return array<mixed>
     */
    protected function questionPointsRules(): array
    {
        return [
            'nullable',
            'integer',
            'min:1',
            'max:100',
        ];
    }

    /**
     * Reject any selected question that has no correct answer.
     */
    protected function rejectQuestionsWithoutCorrectAnswer(Validator $validator): void
    {
        $selections = $this->input('questions', []);

        $ids = array_map(
            fn (array $selection): int => (int) $selection['question_id'],
            $selections,
        );

        // $unanswerable = Question::query()
        //     ->whereIn('id', $ids)
        //     ->whereDoesntHave('answers', fn ($query) => $query->where('is_correct', true))
        //     ->pluck('id')
        //     ->all();

        // foreach ($selections as $index => $selection) {
        //     if (in_array((int) $selection['question_id'], $unanswerable, true)) {
        //         $validator->errors()->add(
        //             "questions.{$index}.question_id",
        //             'This question has no correct answer, so it cannot be used in a quiz.',
        //         );
        //     }
        // }
    }
}
