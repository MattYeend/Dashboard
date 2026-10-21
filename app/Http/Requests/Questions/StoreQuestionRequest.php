<?php

declare(strict_types=1);

namespace App\Http\Requests\Questions;

use App\Actions\Questions\ResolveQuestionableAction;
use App\Enums\QuestionType;
use App\Models\Question;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Question::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'questionable_type' => $this->questionableTypeRules(),
            'questionable_id' => $this->questionableIdRules(),
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
            'questionable_type.required' => 'The question owner type is required.',
            'questionable_type.string' => 'The question owner type must be a string.',
            'questionable_type.in' => 'The selected question owner type is invalid.',
            'questionable_id.required' => 'The question owner is required.',
            'questionable_id.integer' => 'The question owner ID must be an integer.',
            'questionable_id.min' => 'The question owner ID must be at least 1.',
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
     * Get validation rules for the questionable_type field.
     *
     * @return array<mixed>
     */
    protected function questionableTypeRules(): array
    {
        return [
            'required',
            'string',
            Rule::in(array_keys(config('questions.questionable_types', []))),
        ];
    }

    /**
     * Get validation rules for the questionable_id field.
     *
     * @return array<mixed>
     */
    protected function questionableIdRules(): array
    {
        return [
            'required',
            'integer',
            'min:1',
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
            'boolean',
        ];
    }

    /**
     * Perform additional validation after the standard rules have passed.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['questionable_type', 'questionable_id'])) {
                    return;
                }

                $found = app(ResolveQuestionableAction::class)(
                    $this->input('questionable_type'),
                    $this->input('questionable_id'),
                );

                if ($found === null) {
                    $validator->errors()->add(
                        'questionable_id',
                        'The selected question owner does not exist.'
                    );
                }
            },
        ];
    }
}
