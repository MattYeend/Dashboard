<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ticket'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => $this->titleRules(),
            'description' => $this->descriptionRules(),
            'ticket_status_id' => $this->ticketStatusIdRules(),
            'ticket_priority_id' => $this->ticketPriorityIdRules(),
            'assigned_to' => $this->assignedToRules(),
            'due_date' => $this->dueDateRules(),
            'meta' => $this->metaRules(),
            'label_ids' => $this->labelIdsRules(),
            'label_ids.*' => $this->labelIdRules(),
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
            'title.required' => 'The ticket title is required.',
            'title.string' => 'The ticket title must be a string.',
            'title.max' => 'The ticket title may not exceed 255 characters.',
            'description.required' => 'The ticket description is required.',
            'description.string' => 'The ticket description must be a string.',
            'ticket_status_id.exists' => 'The selected ticket status is invalid.',
            'ticket_priority_id.exists' => 'The selected ticket priority is invalid.',
            'assigned_to.exists' => 'The selected assignee is invalid.',
            'due_date.date' => 'The due date must be a valid date.',
            'label_ids.array' => 'Labels must be provided as a list.',
            'label_ids.*.exists' => 'One or more selected labels are invalid.',
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
            'sometimes',
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
            'sometimes',
            'required',
            'string',
        ];
    }

    /**
     * Get validation rules for the ticket_status_id field.
     *
     * @return array<mixed>
     */
    protected function ticketStatusIdRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'exists:ticket_statuses,id',
        ];
    }

    /**
     * Get validation rules for the ticket_priority_id field.
     *
     * @return array<mixed>
     */
    protected function ticketPriorityIdRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'exists:ticket_priorities,id',
        ];
    }

    /**
     * Get validation rules for the assigned_to field.
     *
     * @return array<mixed>
     */
    protected function assignedToRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'exists:users,id',
        ];
    }

    /**
     * Get validation rules for the due_date field.
     *
     * @return array<mixed>
     */
    protected function dueDateRules(): array
    {
        return [
            'sometimes',
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
            'sometimes',
            'nullable',
            'array',
        ];
    }

    /**
     * Get validation rules for the label_ids field.
     *
     * @return array<mixed>
     */
    protected function labelIdsRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'array',
        ];
    }

    /**
     * Get validation rules for each label_ids entry.
     *
     * @return array<mixed>
     */
    protected function labelIdRules(): array
    {
        return [
            'integer',
            'exists:labels,id',
        ];
    }
}
