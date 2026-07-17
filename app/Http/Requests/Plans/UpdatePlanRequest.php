<?php

namespace App\Http\Requests\Plans;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('plan'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'slug' => $this->slugRules(),
            'description' => $this->descriptionRules(),
            'price_per_user_per_month' => $this->pricePerUserPerMonthRules(),
            'is_active' => $this->isActiveRules(),
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
            'name.required' => 'The plan name is required.',
            'name.string' => 'The plan name must be a string.',
            'name.max' => 'The plan name may not exceed 255 characters.',
            'name.unique' => 'A plan with this name already exists.',
            'slug.unique' => 'This slug is already in use.',
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and hyphens.',
            'price_per_user_per_month.required' => 'The price per user per month is required.',
            'price_per_user_per_month.integer' => 'The price must be a whole number of pence.',
            'price_per_user_per_month.min' => 'The price cannot be negative.',
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
            'required',
            'string',
            'max:255',
            Rule::unique('plans', 'name')->ignore($this->route('plan')),
        ];
    }

    /**
     * Get validation rules for the slug field.
     *
     * @return array<mixed>
     */
    protected function slugRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:255',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            Rule::unique('plans', 'slug')->ignore($this->route('plan')),
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
            'nullable',
            'string',
        ];
    }

    /**
     * Get validation rules for the price_per_user_per_month field.
     *
     * @return array<mixed>
     */
    protected function pricePerUserPerMonthRules(): array
    {
        return [
            'sometimes',
            'required',
            'integer',
            'min:0',
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
            'sometimes',
            'boolean',
        ];
    }
}
