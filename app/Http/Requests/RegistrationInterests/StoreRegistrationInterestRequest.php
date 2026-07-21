<?php

namespace App\Http\Requests\RegistrationInterests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationInterestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This is a public endpoint - anyone may register interest.
     */
    public function authorize(): bool
    {
        return true;
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
            'email' => $this->emailRules(),
            'phone' => $this->phoneRules(),
            'company' => $this->companyRules(),
            'message' => $this->messageRules(),
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
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not exceed 255 characters.',
            'email.required' => 'The email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'The email may not exceed 255 characters.',
            'phone.max' => 'The phone number may not exceed 30 characters.',
            'company.max' => 'The company name may not exceed 255 characters.',
            'message.max' => 'The message may not exceed 2000 characters.',
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
            'required',
            'string',
            'max:255',
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
            'required',
            'string',
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
            'nullable',
            'string',
            'max:30',
        ];
    }

    /**
     * Get validation rules for the company field.
     *
     * @return array<mixed>
     */
    protected function companyRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the message field.
     *
     * @return array<mixed>
     */
    protected function messageRules(): array
    {
        return [
            'nullable',
            'string',
            'max:2000',
        ];
    }
}
