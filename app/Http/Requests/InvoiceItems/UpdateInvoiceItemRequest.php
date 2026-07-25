<?php

namespace App\Http\Requests\InvoiceItems;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('invoiceItem'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => $this->descriptionRules(),
            'quantity' => $this->quantityRules(),
            'unit_price' => $this->unitPriceRules(),
            'tax_rate' => $this->taxRateRules(),
            'position' => $this->positionRules(),
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
            'description.required' => 'The item description is required.',
            'description.string' => 'The item description must be a string.',
            'description.max' => 'The item description may not exceed 255 characters.',
            'quantity.integer' => 'The quantity must be a whole number.',
            'quantity.min' => 'The quantity must be at least 1.',
            'unit_price.integer' => 'The unit price must be a whole number of pence.',
            'unit_price.min' => 'The unit price cannot be negative.',
            'tax_rate.numeric' => 'The tax rate must be a number.',
            'tax_rate.min' => 'The tax rate cannot be negative.',
            'tax_rate.max' => 'The tax rate cannot exceed 100%.',
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
            'max:255',
        ];
    }

    /**
     * Get validation rules for the quantity field.
     *
     * @return array<mixed>
     */
    protected function quantityRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'min:1',
        ];
    }

    /**
     * Get validation rules for the unit_price field.
     *
     * @return array<mixed>
     */
    protected function unitPriceRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'min:0',
        ];
    }

    /**
     * Get validation rules for the tax_rate field.
     *
     * @return array<mixed>
     */
    protected function taxRateRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'numeric',
            'min:0',
            'max:100',
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
