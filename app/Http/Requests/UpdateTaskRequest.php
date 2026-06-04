<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'         => $this->titleRules(),
            'description'   => $this->descriptionRules(),
            'due_date'      => $this->dueDateRules(),
            'assigned_date' => $this->assignedDateRules(),
            'assigned_to'   => $this->assignedToRules(),
            'status_id'     => $this->statusIdRules(),
            'meta'          => $this->metaRules(),
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
            'title.required'      => 'The title is required.',
            'title.string'        => 'The title must be a string.',
            'title.max'           => 'The title may not exceed 255 characters.',
            'due_date.date'       => 'The due date must be a valid date.',
            'assigned_date.date'  => 'The assigned date must be a valid date.',
            'assigned_to.exists'  => 'The selected user does not exist.',
            'status_id.exists'    => 'The selected status does not exist.',
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
            'nullable',
            'string',
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
     * Get validation rules for the assigned_date field.
     *
     * @return array<mixed>
     */
    protected function assignedDateRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'date',
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
     * Get validation rules for the status_id field.
     *
     * @return array<mixed>
     */
    protected function statusIdRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'exists:task_statuses,id',
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
}
