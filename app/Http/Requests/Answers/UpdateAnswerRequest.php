<?php

declare(strict_types=1);

namespace App\Http\Requests\Answers;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('answer')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The parent question cannot be changed once an answer exists.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
            'answer.required' => 'Please enter an answer.',
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
            'sometimes',
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
            'sometimes',
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
            'sometimes',
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
            'sometimes',
            'nullable',
            'integer',
            'min:0',
            'max:65535',
        ];
    }
}
