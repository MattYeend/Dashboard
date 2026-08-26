<?php

namespace App\Http\Requests\InteractionLogs;

use App\Enums\InteractionLogType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInteractionLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('interaction_log'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => $this->typeRules(),
            'subject' => $this->subjectRules(),
            'outcome' => $this->outcomeRules(),
            'notes' => $this->notesRules(),
            'occurred_at' => $this->occurredAtRules(),
            'contact_id' => $this->contactIdRules(),
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
            'type.in' => 'The interaction type must be either call or email.',
            'subject.max' => 'The subject may not exceed 255 characters.',
            'occurred_at.before_or_equal' => 'The occurred at date cannot be in the future.',
            'contact_id.exists' => 'The selected contact could not be found.',
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
            'string',
            Rule::in(InteractionLogType::values()),
        ];
    }

    /**
     * Get validation rules for the subject field.
     *
     * @return array<mixed>
     */
    protected function subjectRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the outcome field.
     *
     * @return array<mixed>
     */
    protected function outcomeRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
        ];
    }

    /**
     * Get validation rules for the notes field.
     *
     * @return array<mixed>
     */
    protected function notesRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
        ];
    }

    /**
     * Get validation rules for the occurred_at field.
     *
     * @return array<mixed>
     */
    protected function occurredAtRules(): array
    {
        return [
            'sometimes',
            'required',
            'date',
            'before_or_equal:now',
        ];
    }

    /**
     * Get validation rules for the contact_id field.
     *
     * @return array<mixed>
     */
    protected function contactIdRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'exists:contacts,id',
        ];
    }
}
