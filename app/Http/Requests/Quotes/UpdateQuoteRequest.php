<?php

declare(strict_types=1);

namespace App\Http\Requests\Quotes;

use App\Models\Organisation;
use App\Models\Quote;
use App\Models\User;
use App\Services\Quotes\QuotableTypeRegistryService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateQuoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('quote'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quotable_type' => $this->quotableTypeRules(),
            'quotable_id' => $this->quotableIdRules(),
            'title' => $this->titleRules(),
            'description' => $this->descriptionRules(),
            'notes' => $this->notesRules(),
            'terms' => $this->termsRules(),
            'subtotal' => $this->moneyRules(required: true),
            'discount_amount' => $this->moneyRules(required: false),
            'tax_amount' => $this->moneyRules(required: false),
            'currency' => $this->currencyRules(),
            'issued_at' => $this->issuedAtRules(),
            'valid_until' => $this->validUntilRules(),
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
            'quotable_type.required_with' => 'The customer type is required when a customer is supplied.',
            'quotable_id.required_with' => 'The customer is required when a customer type is supplied.',
            'title.required' => 'The title is required.',
            'title.max' => 'The title may not exceed 255 characters.',
            'subtotal.min' => 'The subtotal cannot be negative.',
            'discount_amount.min' => 'The discount amount cannot be negative.',
            'tax_amount.min' => 'The tax amount cannot be negative.',
            'currency.in' => 'Please select a valid currency.',
            'valid_until.after_or_equal' => 'The valid until date cannot be before the issue date.',
        ];
    }

    /**
     * Get validation rules for the quotable_type field.
     *
     * @return array<mixed>
     */
    protected function quotableTypeRules(): array
    {
        return [
            'required_with:quotable_id',
            'string',
            Rule::in(array_keys(app(QuotableTypeRegistryService::class)->all())),
        ];
    }

    /**
     * Get validation rules for the quotable_id field.
     *
     * @return array<mixed>
     */
    protected function quotableIdRules(): array
    {
        return [
            'required_with:quotable_type',
            'integer',
            'min:1',
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
            'nullable',
            'string',
            'max:5000',
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
            'max:5000',
        ];
    }

    /**
     * Get validation rules for the terms field.
     *
     * @return array<mixed>
     */
    protected function termsRules(): array
    {
        return [
            'nullable',
            'string',
            'max:10000',
        ];
    }

    /**
     * Get validation rules shared by the three money fields.
     *
     * @return array<mixed>
     */
    protected function moneyRules(bool $required): array
    {
        return [
            ...($required ? ['sometimes', 'required'] : ['nullable']),
            'numeric',
            'min:0',
            'max:99999999.99',
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
            'sometimes',
            'required',
            'string',
            'size:3',
            Rule::in(config('currencies.allowed')),
        ];
    }

    /**
     * Get validation rules for the issued_at field.
     *
     * @return array<mixed>
     */
    protected function issuedAtRules(): array
    {
        return [
            'nullable',
            'date',
        ];
    }

    /**
     * Get validation rules for the valid_until field.
     *
     * @return array<mixed>
     */
    protected function validUntilRules(): array
    {
        return [
            'nullable',
            'date',
            'after_or_equal:issued_at',
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
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->validateCustomerExists($validator);
                $this->validateDiscountWithinSubtotal($validator);
            },
        ];
    }

    /**
     * The customer must exist inside the current organisation.
     */
    protected function validateCustomerExists(Validator $validator): void
    {
        if (! $this->filled('quotable_type') || ! $this->filled('quotable_id')) {
            return;
        }

        $modelClass = app(QuotableTypeRegistryService::class)
            ->modelClassForKey((string) $this->input('quotable_type'));

        if (! $modelClass) {
            return;
        }

        $id = (int) $this->input('quotable_id');

        $exists = $modelClass === User::class
            ? (Organisation::current()?->activeUsers()->whereKey($id)->exists() ?? false)
            : $modelClass::whereKey($id)->exists();

        if (! $exists) {
            $validator->errors()->add('quotable_id', 'The selected customer does not exist.');
        }
    }

    /**
     * A header discount cannot exceed the subtotal.
     * Values missing from the request fall back to the stored quote.
     */
    protected function validateDiscountWithinSubtotal(Validator $validator): void
    {
        /** @var Quote $quote */
        $quote = $this->route('quote');

        $subtotal = (string) ($this->has('subtotal') ? $this->input('subtotal') : $quote->subtotal);
        $discount = (string) ($this->has('discount_amount') ? $this->input('discount_amount') : $quote->discount_amount);

        if (bccomp($discount === '' ? '0' : $discount, $subtotal === '' ? '0' : $subtotal, 2) === 1) {
            $validator->errors()->add('discount_amount', 'The discount cannot exceed the subtotal.');
        }
    }
}
