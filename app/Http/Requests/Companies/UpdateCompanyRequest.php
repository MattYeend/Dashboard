<?php

namespace App\Http\Requests\Companies;

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
            'slug' => $this->slugRules(),
            'registration_number' => $this->registrationNumberRules(),
            'vat_number' => $this->vatNumberRules(),
            'industry_id' => $this->industryIdRules(),
            'account_manager_id' => $this->accountManagerIdRules(),
            'email' => $this->emailRules(),
            'phone' => $this->phoneRules(),
            'website' => $this->websiteRules(),
            'description' => $this->descriptionRules(),
            'employee_count' => $this->employeeCountRules(),
            'founded_year' => $this->foundedYearRules(),
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
            'slug.unique' => 'This slug is already in use.',
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and hyphens.',
            'vat_number.string' => 'The VAT number must be a string.',
            'employee_count.integer' => 'The employee count must be a whole number.',
            'founded_year.digits' => 'The founded year must be a 4-digit year.',
            'registration_number.max' => 'The registration number may not exceed 255 characters.',
            'registration_number.unique' => 'This registration number is already in use.',
            'industry_id.exists' => 'The selected industry does not exist.',
            'account_manager_id.exists' => 'The selected account manager does not exist.',
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
            Rule::unique('companies', 'slug')->ignore($this->route('company')),
        ];
    }

    /**
     * Get validation rules for the vat_number field.
     *
     * @return array<mixed>
     */
    protected function vatNumberRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the employee_count field.
     *
     * @return array<mixed>
     */
    protected function employeeCountRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'min:0',
        ];
    }

    /**
     * Get validation rules for the founded_year field.
     *
     * @return array<mixed>
     */
    protected function foundedYearRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'digits:4',
            'min:1800',
            'max:'.date('Y'),
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
     * Get validation rules for the account_manager_id field.
     *
     * @return array<mixed>
     */
    protected function accountManagerIdRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'exists:users,id',
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
