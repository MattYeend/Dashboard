<?php

declare(strict_types=1);

namespace App\Http\Requests\PaymentStatuses;

use App\Models\PaymentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\Multitenancy\Models\Tenant;

class UpdatePaymentStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('paymentStatus')) ?? false;
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
            'background_colour' => $this->backgroundColourRules(),
            'text_colour' => $this->textColourRules(),
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
            'title.unique' => 'A payment status with that title already exists.',
            'background_colour.regex' => 'The background colour must be a hex value such as #e2e8f0.',
            'text_colour.regex' => 'The text colour must be a hex value such as #1a202c.',
        ];
    }

    /**
     * Perform additional validation after the standard rules have passed.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var PaymentStatus|null $status */
                $status = $this->route('paymentStatus');

                if (
                    $status?->isSystem()
                    && $this->has('title')
                    && $this->input('title') !== $status->title
                ) {
                    $validator->errors()->add('title', 'System statuses cannot be renamed.');
                }
            },
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
            Rule::unique('payment_statuses', 'title')
                ->where('organisation_id', Tenant::current()?->id)
                ->ignore($this->route('paymentStatus')?->id),
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
            'max:1000',
        ];
    }

    /**
     * Get validation rules for the background_colour field.
     *
     * @return array<mixed>
     */
    protected function backgroundColourRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            'regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }

    /**
     * Get validation rules for the text_colour field.
     *
     * @return array<mixed>
     */
    protected function textColourRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            'regex:/^#[0-9A-Fa-f]{6}$/',
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
