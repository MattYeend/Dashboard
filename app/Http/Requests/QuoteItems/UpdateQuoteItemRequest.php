<?php

declare(strict_types=1);

namespace App\Http\Requests\QuoteItems;

use App\Models\Product;
use App\Models\Quote;
use App\Rules\TenantRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateQuoteItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('quoteItem'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * line_total, line_tax, position and quote_id are deliberately absent.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => $this->productIdRules(),
            'name' => $this->nameRules(),
            'description' => $this->descriptionRules(),
            'unit' => $this->unitRules(),
            'quantity' => $this->quantityRules(),
            'unit_price' => $this->unitPriceRules(),
            'discount_percent' => $this->discountPercentRules(),
            'tax_rate' => $this->taxRateRules(),
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
            'product_id.integer' => 'The product must be a valid product.',
            'name.required_without' => 'Enter a name or choose a product.',
            'name.max' => 'The name may not exceed 255 characters.',
            'quantity.required' => 'The quantity is required.',
            'quantity.min' => 'The quantity must be greater than zero.',
            'unit_price.required_without' => 'Enter a unit price or choose a product.',
            'unit_price.min' => 'The unit price cannot be negative.',
            'discount_percent.max' => 'The discount cannot exceed 100 per cent.',
            'tax_rate.max' => 'The tax rate cannot exceed 100 per cent.',
            'unit.in' => 'Please select a valid unit.',
        ];
    }

    /**
     * Get validation rules for the product_id field.
     *
     * @return array<mixed>
     */
    protected function productIdRules(): array
    {
        return [
            'nullable',
            'integer',
            TenantRules::exists('products'),
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
            'sometimes',
            'required_without:product_id',
            'nullable',
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
            'nullable',
            'string',
            Rule::in(Product::UNITS),
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
            'required',
            'numeric',
            'min:0.01',
            'max:99999999.99',
            'decimal:0,2',
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
            'required_without:product_id',
            'nullable',
            'numeric',
            'min:0',
            'max:99999999.99',
            'decimal:0,2',
        ];
    }

    /**
     * Get validation rules for the discount_percent field.
     *
     * @return array<mixed>
     */
    protected function discountPercentRules(): array
    {
        return [
            'nullable',
            'numeric',
            'min:0',
            'max:100',
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
            'nullable',
            'numeric',
            'min:0',
            'max:100',
            'decimal:0,2',
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

    /**
     * Perform additional validation after the standard rules have passed.
     *
     * A product change on update goes through the same checks as on create.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->filled('product_id')) {
                    return;
                }

                $product = Product::query()->find((int) $this->input('product_id'));
                /** @var Quote $quote */
                $quote = $this->route('quote');

                if ($product === null || ! $product->is_active) {
                    $validator->errors()->add('product_id', 'The selected product is not available.');

                    return;
                }

                if ($product->currency !== $quote->currency) {
                    $validator->errors()->add(
                        'product_id',
                        "The product is priced in {$product->currency} but the quote is in {$quote->currency}."
                    );
                }
            },
        ];
    }
}
