<?php

namespace App\Http\Requests\Pipelines;

use App\Models\Pipeline;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePipelineRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        if (! $this->user()->can('create', Pipeline::class)) {
            return false;
        }

        if ($this->filled('assigned_to') && ! $this->user()->can('assign pipeline')) {
            return false;
        }

        return true;
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
            'is_default' => $this->isDefaultRules(),
            'status_id' => $this->statusIdRules(),
            'assigned_to' => $this->assignedToRules(),
            'meta' => $this->metaRules(),
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
            'title.required' => 'The title is required.',
            'title.max' => 'The title may not exceed 255 characters.',
            'is_default.boolean' => 'The default flag must be true or false.',
            'status_id.exists' => 'The selected status does not exist.',
            'assigned_to.exists' => 'The selected assignee does not exist.',
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
        ];
    }

    /**
     * Get validation rules for the is_default field.
     *
     * @return array<mixed>
     */
    protected function isDefaultRules(): array
    {
        return [
            'nullable',
            'boolean',
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
            'nullable',
            'integer',
            'exists:pipeline_statuses,id',
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
            'nullable',
            'integer',
            'exists:users,id',
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
}
