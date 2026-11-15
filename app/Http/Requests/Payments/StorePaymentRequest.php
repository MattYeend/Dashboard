<?php

declare(strict_types=1);

namespace App\Http\Requests\Payments;

use App\Enums\PaymentMethod;
use App\Models\Payment;
use App\Rules\TenantRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Payment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_id' => $this->invoiceIdRules(),
            'method' => $this->methodRules(),
            'amount' => $this->amountRules(),
            'paid_at' => $this->paidAtRules(),
            'reference' => $this->referenceRules(),
            'notes' => $this->notesRules(),
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
            'invoice_id.required' => 'Please select an invoice.',
            'amount.gt' => 'The amount must be greater than zero.',
            'amount.decimal' => 'The amount may have at most two decimal places.',
            'amount.max' => 'The amount is too large.',
            'paid_at.before_or_equal' => 'The date received cannot be in the future.',
            'reference.unique' => 'A payment with that reference already exists on this invoice.',
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
            'required',
            'integer',
            TenantRules::exists('invoices'),
        ];
    }

    /**
     * Get validation rules for the method field.
     *
     * @return array<mixed>
     */
    protected function methodRules(): array
    {
        return [
            'required',
            Rule::enum(PaymentMethod::class),
        ];
    }

    /**
     * Get validation rules for the amount field.
     *
     * @return array<mixed>
     */
    protected function amountRules(): array
    {
        return [
            'required',
            'numeric',
            'gt:0',
            'max:99999999.99',
            'decimal:0,2',
        ];
    }

    /**
     * Get validation rules for the paid_at field.
     *
     * @return array<mixed>
     */
    protected function paidAtRules(): array
    {
        return [
            'required',
            'date',
            'before_or_equal:now',
        ];
    }

    /**
     * Get validation rules for the reference field.
     *
     * @return array<mixed>
     */
    protected function referenceRules(): array
    {
        return [
            'nullable',
            'string',
            'max:128',
            Rule::unique('payments', 'reference')
                ->where('invoice_id', $this->input('invoice_id')),
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
            'max:2000',
        ];
    }
}