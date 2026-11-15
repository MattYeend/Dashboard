<?php

declare(strict_types=1);

namespace App\Http\Requests\Payments;

use App\Enums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('payment'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'method' => $this->methodRules(),
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
            'paid_at.before_or_equal' => 'The date received cannot be in the future.',
            'reference.unique' => 'A payment with that reference already exists on this invoice.',
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
            'sometimes',
            'required',
            Rule::enum(PaymentMethod::class),
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
            'sometimes',
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
                ->where('invoice_id', $this->route('payment')->invoice_id)
                ->ignore($this->route('payment')),
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
