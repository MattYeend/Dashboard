<?php

namespace App\Http\Requests\Reports;

use App\Models\Report;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Report::class);
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
            'type' => $this->typeRules(),
            'format' => $this->formatRules(),
            'filters' => $this->filtersRules(),
            'is_scheduled' => $this->isScheduledRules(),
            'schedule_frequency' => $this->scheduleFrequencyRules(),
            'schedule_time' => $this->scheduleTimeRules(),
            'recipients' => $this->recipientsRules(),
            'recipients.*' => $this->recipientRules(),
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
            'title.required' => 'The report title is required.',
            'title.max' => 'The report title may not exceed 255 characters.',
            'type.required' => 'Please select what the report should cover.',
            'format.in' => 'The format must be one of: pdf, csv, xlsx.',
            'schedule_frequency.required_if' => 'A schedule frequency is required when the report is scheduled.',
            'schedule_time.required_if' => 'A schedule time is required when the report is scheduled.',
            'recipients.*.email' => 'One or more recipient email addresses are invalid.',
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
     * Get validation rules for the type field.
     *
     * @return array<mixed>
     */
    protected function typeRules(): array
    {
        return [
            'required',
            'string',
            'max:100',
        ];
    }

    /**
     * Get validation rules for the format field.
     *
     * @return array<mixed>
     */
    protected function formatRules(): array
    {
        return [
            'required',
            'string',
            'in:pdf,csv,xlsx',
        ];
    }

    /**
     * Get validation rules for the filters field.
     *
     * @return array<mixed>
     */
    protected function filtersRules(): array
    {
        return [
            'nullable',
            'array',
        ];
    }

    /**
     * Get validation rules for the is_scheduled field.
     *
     * @return array<mixed>
     */
    protected function isScheduledRules(): array
    {
        return [
            'required',
            'boolean',
        ];
    }

    /**
     * Get validation rules for the schedule_frequency field.
     *
     * @return array<mixed>
     */
    protected function scheduleFrequencyRules(): array
    {
        return [
            'nullable',
            'required_if:is_scheduled,true',
            'string',
            'in:daily,weekly,monthly',
        ];
    }

    /**
     * Get validation rules for the schedule_time field.
     *
     * @return array<mixed>
     */
    protected function scheduleTimeRules(): array
    {
        return [
            'nullable',
            'required_if:is_scheduled,true',
            'date_format:H:i',
        ];
    }

    /**
     * Get validation rules for the recipients field.
     *
     * @return array<mixed>
     */
    protected function recipientsRules(): array
    {
        return [
            'nullable',
            'array',
        ];
    }

    /**
     * Get validation rules for each recipients entry.
     *
     * @return array<mixed>
     */
    protected function recipientRules(): array
    {
        return [
            'email',
        ];
    }
}
