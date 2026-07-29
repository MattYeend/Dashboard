<?php

namespace App\Http\Requests\Deals;

use App\Models\Deal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDealRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Deal::class);
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
            'pipeline_id' => $this->pipelineIdRules(),
            'stage_id' => $this->stageIdRules(),
            'status_id' => $this->statusIdRules(),
            'company_id' => $this->companyIdRules(),
            'invoice_id' => $this->invoiceIdRules(),
            'value' => $this->valueRules(),
            'currency' => $this->currencyRules(),
            'probability' => $this->probabilityRules(),
            'expected_close_date' => $this->expectedCloseDateRules(),
            'closed_at' => $this->closedAtRules(),
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
            'pipeline_id.exists' => 'The selected pipeline does not exist.',
            'stage_id.exists' => 'The selected stage does not exist.',
            'status_id.exists' => 'The selected status does not exist.',
            'company_id.exists' => 'The selected company does not exist.',
            'invoice_id.exists' => 'The selected invoice does not exist.',
            'invoice_id.unique' => 'That invoice is already linked to another deal.',
            'value.min' => 'The value cannot be negative.',
            'currency.size' => 'The currency must be a 3-letter code, e.g. GBP.',
            'probability.min' => 'The probability cannot be less than 0.',
            'probability.max' => 'The probability cannot be greater than 100.',
            'closed_at.date' => 'The closed date must be a valid date.',
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
     * Get validation rules for the pipeline_id field.
     *
     * @return array<mixed>
     */
    protected function pipelineIdRules(): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('pipelines', 'id')->whereNull('deleted_at'),
        ];
    }

    /**
     * Get validation rules for the stage_id field.
     *
     * @return array<mixed>
     */
    protected function stageIdRules(): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('pipeline_stages', 'id')->whereNull('deleted_at'),
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
            Rule::exists('deal_statuses', 'id')->whereNull('deleted_at'),
        ];
    }

    /**
     * Get validation rules for the company_id field.
     *
     * @return array<mixed>
     */
    protected function companyIdRules(): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('companies', 'id')->whereNull('deleted_at'),
        ];
    }

    /**
     * Get validation rules for the invoice_id field.
     *
     * @return array<mixed>
     */
    protected function invoiceIdRules(): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('invoices', 'id')->whereNull('deleted_at'),
            Rule::unique('deals', 'invoice_id'),
        ];
    }

    /**
     * Get validation rules for the value field.
     *
     * @return array<mixed>
     */
    protected function valueRules(): array
    {
        return [
            'nullable',
            'integer',
            'min:0',
        ];
    }

    /**
     * Get validation rules for the currency field.
     *
     * @return array<mixed>
     */
    protected function currencyRules(): array
    {
        return [
            'nullable',
            'string',
            'size:3',
        ];
    }

    /**
     * Get validation rules for the probability field.
     *
     * @return array<mixed>
     */
    protected function probabilityRules(): array
    {
        return [
            'nullable',
            'integer',
            'min:0',
            'max:100',
        ];
    }

    /**
     * Get validation rules for the expected_close_date field.
     *
     * @return array<mixed>
     */
    protected function expectedCloseDateRules(): array
    {
        return [
            'nullable',
            'date',
        ];
    }

    /**
     * Get validation rules for the closed_at field.
     *
     * @return array<mixed>
     */
    protected function closedAtRules(): array
    {
        return [
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
            'nullable',
            'array',
        ];
    }
}