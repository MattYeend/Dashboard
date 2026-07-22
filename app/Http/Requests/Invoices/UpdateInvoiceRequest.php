<?php

namespace App\Http\Requests\Invoices;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('invoice'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_number' => $this->invoiceNumberRules(),
            'company_id' => $this->companyIdRules(),
            'order_id' => $this->orderIdRules(),
            'status_id' => $this->statusIdRules(),
            'issue_date' => $this->issueDateRules(),
            'due_date' => $this->dueDateRules(),
            'subtotal' => $this->subtotalRules(),
            'tax_total' => $this->taxTotalRules(),
            'total' => $this->totalRules(),
            'currency' => $this->currencyRules(),
            'notes' => $this->notesRules(),
            'meta' => $this->metaRules(),
            'contact' => $this->contactRules(),
            'contact.phone' => $this->contactPhoneRules(),
            'contact.email' => $this->contactEmailRules(),
            'contact.address' => $this->contactAddressRules(),
            'contact.city' => $this->contactCityRules(),
            'contact.postal_code' => $this->contactPostalCodeRules(),
            'contact.country' => $this->contactCountryRules(),
            'contact.meta' => $this->contactMetaRules(),
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
            'invoice_number.required' => 'The invoice number is required.',
            'invoice_number.unique' => 'This invoice number is already in use.',
            'company_id.exists' => 'The selected company does not exist.',
            'order_id.exists' => 'The selected order does not exist.',
            'status_id.exists' => 'The selected status does not exist.',
            'issue_date.date' => 'The issue date must be a valid date.',
            'due_date.date' => 'The due date must be a valid date.',
            'due_date.after_or_equal' => 'The due date must be on or after the issue date.',
            'currency.size' => 'The currency must be a 3-letter code, e.g. GBP.',
            'contact.email.email' => 'The contact email must be a valid email address.',
            'company_id.required_without' => 'Either a company or an order must be selected.',
            'order_id.required_without' => 'Either a company or an order must be selected.',
        ];
    }

    /**
     * Get validation rules for the invoice_number field.
     *
     * @return array<mixed>
     */
    protected function invoiceNumberRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            'max:255',
            Rule::unique('invoices', 'invoice_number')->ignore($this->route('invoice')),
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
            'sometimes',
            'required_without:order_id',
            'nullable',
            'integer',
            'exists:companies,id',
        ];
    }

    /**
     * Get validation rules for the order_id field.
     *
     * @return array<mixed>
     */
    protected function orderIdRules(): array
    {
        return [
            'sometimes',
            'required_without:company_id',
            'nullable',
            'integer',
            'exists:orders,id',
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
            'exists:invoice_statuses,id',
        ];
    }

    /**
     * Get validation rules for the issue_date field.
     *
     * @return array<mixed>
     */
    protected function issueDateRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'date',
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
            'after_or_equal:issue_date',
        ];
    }

    /**
     * Get validation rules for the subtotal field.
     *
     * @return array<mixed>
     */
    protected function subtotalRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'min:0',
        ];
    }

    /**
     * Get validation rules for the tax_total field.
     *
     * @return array<mixed>
     */
    protected function taxTotalRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'min:0',
        ];
    }

    /**
     * Get validation rules for the total field.
     *
     * @return array<mixed>
     */
    protected function totalRules(): array
    {
        return [
            'sometimes',
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
            'sometimes',
            'nullable',
            'string',
            'size:3',
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
            'sometimes',
            'nullable',
            'string',
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
     * Get validation rules for the contact field.
     *
     * @return array<mixed>
     */
    protected function contactRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'array',
        ];
    }

    /**
     * Get validation rules for the contact.phone field.
     *
     * @return array<mixed>
     */
    protected function contactPhoneRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the contact.email field.
     *
     * @return array<mixed>
     */
    protected function contactEmailRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'email',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the contact.address field.
     *
     * @return array<mixed>
     */
    protected function contactAddressRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the contact.city field.
     *
     * @return array<mixed>
     */
    protected function contactCityRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the contact.postal_code field.
     *
     * @return array<mixed>
     */
    protected function contactPostalCodeRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the contact.country field.
     *
     * @return array<mixed>
     */
    protected function contactCountryRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the contact.meta field.
     *
     * @return array<mixed>
     */
    protected function contactMetaRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'array',
        ];
    }
}
