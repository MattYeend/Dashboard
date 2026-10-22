<?php

declare(strict_types=1);

namespace App\Http\Requests\Answers;

use App\Models\Answer;
use App\Models\Organisation;
use App\Models\Question;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     *
     * The user must be able to create answers and update the parent
     * question, so answers cannot be attached to questions they cannot manage.
     */
    public function authorize(): bool
    {
        if (! ($this->user()?->can('create', Answer::class) ?? false)) {
            return false;
        }

        $question = Question::query()->find($this->input('question_id'));

        return $question === null || $this->user()->can('update', $question);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_id' => $this->questionRules(),
            'answer' => $this->answerRules(),
            'is_correct' => $this->isCorrectRules(),
            'feedback' => $this->feedbackRules(),
            'position' => $this->positionRules(),
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'question_id.required' => 'Please choose a question.',
            'question_id.exists' => 'The selected question could not be found.',
            'answer.required' => 'Please enter an answer.',
        ];
    }

    /**
     * Rules for the question field, limited to active questions in the
     * current organisation.
     *
     * @return array<int, mixed>
     */
    protected function questionRules(): array
    {
        return [
            'required',
            'integer',
            Rule::exists('questions', 'id')
                ->where('organisation_id', Organisation::current()?->id)
                ->whereNull('deleted_at'),
        ];
    }

    /**
     * Rules for the answer field.
     *
     * @return array<int, mixed>
     */
    protected function answerRules(): array
    {
        return [
            'required',
            'string',
            'max:2000',
        ];
    }

    /**
     * Rules for the is correct field.
     *
     * @return array<int, mixed>
     */
    protected function isCorrectRules(): array
    {
        return [
            'boolean',
        ];
    }

    /**
     * Rules for the feedback field.
     *
     * @return array<int, mixed>
     */
    protected function feedbackRules(): array
    {
        return [
            'nullable',
            'string',
            'max:5000',
        ];
    }

    /**
     * Rules for the position field.
     *
     * @return array<int, mixed>
     */
    protected function positionRules(): array
    {
        return [
            'nullable',
            'integer',
            'min:0',
            'max:65535',
        ];
    }
}
