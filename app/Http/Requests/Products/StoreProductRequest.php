<?php

declare(strict_types=1);

namespace App\Http\Requests\Products;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Multitenancy\Models\Tenant;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sku' => $this->skuRules(),
            'name' => $this->nameRules(),
            'description' => $this->descriptionRules(),
            'unit' => $this->unitRules(),
            'unit_price' => $this->unitPriceRules(),
            'cost_price' => $this->costPriceRules(),
            'tax_rate' => $this->taxRateRules(),
            'currency' => $this->currencyRules(),
            'is_active' => $this->isActiveRules(),
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
            'sku.required' => 'The SKU is required.',
            'sku.regex' => 'The SKU may only contain letters, numbers, dashes and underscores.',
            'sku.unique' => 'That SKU is already in use.',
            'name.required' => 'The name is required.',
            'name.max' => 'The name may not exceed 255 characters.',
            'unit.in' => 'Please select a valid unit.',
            'unit_price.min' => 'The unit price cannot be negative.',
            'cost_price.min' => 'The cost price cannot be negative.',
            'tax_rate.max' => 'The tax rate cannot exceed 100.',
            'currency.in' => 'Please select a valid currency.',
        ];
    }

    /**
     * Get validation rules for the sku field.
     *
     * @return array<mixed>
     */
    protected function skuRules(): array
    {
        return [
            'required',
            'string',
            'max:64',
            'regex:/^[A-Za-z0-9_\-]+$/',
            Rule::unique('products', 'sku')
                ->where('organisation_id', Tenant::current()?->id),
        ];
    }

    /**
     * Get validation rules for the name field.
     *
     * @return array<mixed>
     */
    protected function nameRules(): array
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
            'max:5000',
        ];
    }

    /**
     * Get validation rules for the unit field.
     *
     * @return array<mixed>
     */
    protected function unitRules(): array
    {
        return [
            'required',
            'string',
            Rule::in(Product::UNITS),
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
            'required',
            'numeric',
            'min:0',
            'max:99999999.99',
            'decimal:0,2',
        ];
    }

    /**
     * Get validation rules for the cost_price field.
     *
     * @return array<mixed>
     */
    protected function costPriceRules(): array
    {
        return [
            'nullable',
            'numeric',
            'min:0',
            'max:99999999.99',
            'decimal:0,2',
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
            'required',
            'numeric',
            'min:0',
            'max:100',
            'decimal:0,2',
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
            'required',
            'string',
            'size:3',
            Rule::in(config('currencies.allowed')),
        ];
    }

    /**
     * Get validation rules for the is_active field.
     *
     * @return array<mixed>
     */
    protected function isActiveRules(): array
    {
        return [
            'required',
            'boolean',
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
