<?php

namespace App\Http\Requests\InteractionLogs;

use App\Enums\InteractionLogType;
use App\Models\InteractionLog;
use App\Services\InteractionLogs\InteractionLoggableTypeRegistryService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInteractionLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', InteractionLog::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(InteractionLoggableTypeRegistryService $registryService): array
    {
        return [
            'interactable_type' => $this->interactableTypeRules($registryService),
            'interactable_id' => $this->interactableIdRules(),
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
            'interactable_type.required' => 'The record this log belongs to is required.',
            'interactable_type.in' => 'The record type is not recognised.',
            'interactable_id.required' => 'The record this log belongs to is required.',
            'type.required' => 'The interaction type is required.',
            'type.in' => 'The interaction type must be either call or email.',
            'subject.required' => 'The subject is required.',
            'subject.max' => 'The subject may not exceed 255 characters.',
            'occurred_at.required' => 'The date the interaction occurred is required.',
            'occurred_at.before_or_equal' => 'The occurred at date cannot be in the future.',
            'contact_id.exists' => 'The selected contact could not be found.',
        ];
    }

    /**
     * Get validation rules for the interactable_type field.
     *
     * @return array<mixed>
     */
    protected function interactableTypeRules(InteractionLoggableTypeRegistryService $registryService): array
    {
        return [
            'required',
            'string',
            Rule::in(array_column($registryService->types(), 'value')),
        ];
    }

    /**
     * Get validation rules for the interactable_id field.
     *
     * @return array<mixed>
     */
    protected function interactableIdRules(): array
    {
        return [
            'required',
            'integer',
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
            'nullable',
            'integer',
            'exists:contacts,id',
        ];
    }
}
