<?php

declare(strict_types=1);

namespace App\Http\Requests\Questions;

use App\Enums\QuestionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('question'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => $this->questionRules(),
            'type' => $this->typeRules(),
            'explanation' => $this->explanationRules(),
            'points' => $this->pointsRules(),
            'position' => $this->positionRules(),
            'is_required' => $this->isRequiredRules(),
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
            'question.required' => 'The question is required.',
            'question.string' => 'The question must be a string.',
            'question.max' => 'The question may not exceed 2000 characters.',
            'type.required' => 'The question type is required.',
            'explanation.string' => 'The explanation must be a string.',
            'explanation.max' => 'The explanation may not exceed 5000 characters.',
            'points.required' => 'The points are required.',
            'points.integer' => 'The points must be a whole number.',
            'points.min' => 'The points cannot be negative.',
            'points.max' => 'The points may not exceed 1000.',
            'position.integer' => 'The position must be a whole number.',
            'position.min' => 'The position cannot be negative.',
            'position.max' => 'The position may not exceed 65535.',
            'is_required.boolean' => 'The required flag must be true or false.',
        ];
    }

    /**
     * Get validation rules for the question field.
     *
     * @return array<mixed>
     */
    protected function questionRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            'max:2000',
        ];
    }

    /**
     * Get validation rules for the type field.
     *
     * @return array<mixed>
     */
    protected function typeRules(): array
    {
        return [
            'sometimes',
            'required',
            Rule::enum(QuestionType::class),
        ];
    }

    /**
     * Get validation rules for the explanation field.
     *
     * @return array<mixed>
     */
    protected function explanationRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:5000',
        ];
    }

    /**
     * Get validation rules for the points field.
     *
     * @return array<mixed>
     */
    protected function pointsRules(): array
    {
        return [
            'sometimes',
            'required',
            'integer',
            'min:0',
            'max:1000',
        ];
    }

    /**
     * Get validation rules for the position field.
     *
     * @return array<mixed>
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

    /**
     * Get validation rules for the is_required field.
     *
     * @return array<mixed>
     */
    protected function isRequiredRules(): array
    {
        return [
            'sometimes',
            'boolean',
        ];
    }
}
