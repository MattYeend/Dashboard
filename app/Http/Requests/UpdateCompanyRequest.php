<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('company'));
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
            'registration_number' => $this->registrationNumberRules(),
            'industry_id' => $this->industryIdRules(),
            'email' => $this->emailRules(),
            'phone' => $this->phoneRules(),
            'website' => $this->websiteRules(),
            'description' => $this->descriptionRules(),
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
            'name.required' => 'The company name is required.',
            'name.string' => 'The company name must be a string.',
            'name.max' => 'The company name may not exceed 255 characters.',
            'registration_number.string' => 'The registration number must be a string.',
            'registration_number.max' => 'The registration number may not exceed 255 characters.',
            'registration_number.unique' => 'This registration number is already in use.',
            'industry_id.exists' => 'The selected industry does not exist.',
            'email.email' => 'Please provide a valid email address.',
            'website.url' => 'Please provide a valid website URL.',
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
        ];
    }

    /**
     * Get validation rules for the registration_number field.
     *
     * @return array<mixed>
     */
    protected function registrationNumberRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:255',
            Rule::unique('companies', 'registration_number')->ignore($this->route('company')),
        ];
    }

    /**
     * Get validation rules for the industry_id field.
     *
     * @return array<mixed>
     */
    protected function industryIdRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'exists:industries,id',
        ];
    }

    /**
     * Get validation rules for the email field.
     *
     * @return array<mixed>
     */
    protected function emailRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'email',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the phone field.
     *
     * @return array<mixed>
     */
    protected function phoneRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:50',
        ];
    }

    /**
     * Get validation rules for the website field.
     *
     * @return array<mixed>
     */
    protected function websiteRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'url',
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
}
