<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\User;
use App\Services\Orders\OrderableTypeRegistryService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Order::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'orderable_type' => $this->orderableTypeRules(),
            'orderable_id' => $this->orderableIdRules(),
            'title' => $this->titleRules(),
            'description' => $this->descriptionRules(),
            'notes' => $this->notesRules(),
            'subtotal' => $this->subtotalRules(),
            'discount_amount' => $this->discountAmountRules(),
            'tax_amount' => $this->taxAmountRules(),
            'total_amount' => $this->totalAmountRules(),
            'ordered_at' => $this->orderedAtRules(),
            'due_at' => $this->dueAtRules(),
            'completed_at' => $this->completedAtRules(),
            'status_id' => $this->statusIdRules(),
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
            'orderable_type.required' => 'The orderable type is required.',
            'orderable_type.string' => 'The orderable type must be a
                 string.',
            'orderable_id.required' => 'The orderable ID is required.',
            'orderable_id.integer' => 'The orderable ID must be an integer.',
            'orderable_id.min' => 'The orderable ID must be at least 1.',
            'order_number.required' => 'The order number is required.',
            'order_number.unique' => 'That order number is already in use.',
            'title.required' => 'The title is required.',
            'title.max' => 'The title may not exceed 255 characters.',
            'subtotal.min' => 'The subtotal cannot be negative.',
            'discount_amount.min' => 'The discount amount cannot be
                 negative.',
            'tax_amount.min' => 'The tax amount cannot be negative.',
            'total_amount.min' => 'The total amount cannot be negative.',
            'status_id.exists' => 'The selected status does not exist.',
        ];
    }

    /**
     * Get validation rules for the orderable_type field.
     *
     * @return array<mixed>
     */
    protected function orderableTypeRules(): array
    {
        return [
            'required',
            'string',
            Rule::in(array_keys(
                app(OrderableTypeRegistryService::class)->all()
            )),
        ];
    }

    /**
     * Get validation rules for the orderable_id field.
     *
     * @return array<mixed>
     */
    protected function orderableIdRules(): array
    {
        return [
            'required',
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
     * Get validation rules for the notes field.
     *
     * @return array<mixed>
     */
    protected function notesRules(): array
    {
        return [
            'nullable',
            'string',
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
            'required',
            'numeric',
            'min:0',
            'max:99999999.99',
        ];
    }

    /**
     * Get validation rules for the discount_amount field.
     *
     * @return array<mixed>
     */
    protected function discountAmountRules(): array
    {
        return [
            'nullable',
            'numeric',
            'min:0',
            'max:99999999.99',
        ];
    }

    /**
     * Get validation rules for the tax_amount field.
     *
     * @return array<mixed>
     */
    protected function taxAmountRules(): array
    {
        return [
            'nullable',
            'numeric',
            'min:0',
            'max:99999999.99',
        ];
    }

    /**
     * Get validation rules for the total_amount field.
     *
     * @return array<mixed>
     */
    protected function totalAmountRules(): array
    {
        return [
            'required',
            'numeric',
            'min:0',
            'max:99999999.99',
        ];
    }

    /**
     * Get validation rules for the ordered_at field.
     *
     * @return array<mixed>
     */
    protected function orderedAtRules(): array
    {
        return [
            'nullable',
            'date',
        ];
    }

    /**
     * Get validation rules for the due_at field.
     *
     * @return array<mixed>
     */
    protected function dueAtRules(): array
    {
        return [
            'nullable',
            'date',
            'after_or_equal:ordered_at',
        ];
    }

    /**
     * Get validation rules for the completed_at field.
     *
     * @return array<mixed>
     */
    protected function completedAtRules(): array
    {
        return [
            'nullable',
            'date',
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
            Rule::exists('order_statuses', 'id'),
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
                if ($this->input('orderable_type') !== 'user') {
                    return;
                }

                if (! User::whereKey($this->input('orderable_id'))->exists()) {
                    $validator->errors()->add(
                        'orderable_id',
                        'The selected order owner does not exist.'
                    );
                }
            },
        ];
    }
}
