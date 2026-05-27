<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Contact::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contactable_type' => $this->contactableTypeRules(),
            'contactable_id' => $this->contactableIdRules(),
            'phone' => $this->phoneRules(),
            'email' => $this->emailRules(),
            'address' => $this->addressRules(),
            'city' => $this->cityRules(),
            'postal_code' => $this->postalCodeRules(),
            'country' => $this->countryRules(),
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
            'contactable_type.required' => 'The contactable type is required.',
            'contactable_type.string' => 'The contactable type must be a string.',
            'contactable_id.required' => 'The contactable ID is required.',
            'contactable_id.integer' => 'The contactable ID must be an integer.',
            'contactable_id.min' => 'The contactable ID must be at least 1.',
            'email.email' => 'The email address must be a valid email.',
            'email.max' => 'The email address may not exceed 255 characters.',
            'phone.max' => 'The phone number may not exceed 255 characters.',
            'address.max' => 'The address may not exceed 255 characters.',
            'city.max' => 'The city may not exceed 255 characters.',
            'postal_code.max' => 'The postal code may not exceed 255 characters.',
            'country.max' => 'The country may not exceed 255 characters.',
        ];
    }

    /**
     * Get validation rules for the contactable_type field.
     *
     * @return array<mixed>
     */
    protected function contactableTypeRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the contactable_id field.
     *
     * @return array<mixed>
     */
    protected function contactableIdRules(): array
    {
        return [
            'required',
            'integer',
            'min:1',
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
            'nullable',
            'email',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the address field.
     *
     * @return array<mixed>
     */
    protected function addressRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the city field.
     *
     * @return array<mixed>
     */
    protected function cityRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the postal_code field.
     *
     * @return array<mixed>
     */
    protected function postalCodeRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the country field.
     *
     * @return array<mixed>
     */
    protected function countryRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
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
