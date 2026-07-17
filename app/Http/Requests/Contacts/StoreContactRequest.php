<?php

namespace App\Http\Requests\Contacts;

use App\Models\Contact;
use App\Services\Contacts\ContactableTypeRegistryService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'contactable_type.string' => 'The contactable type must be a
                 string.',
            'contactable_id.required' => 'The contactable ID is required.',
            'contactable_id.integer' => 'The contactable ID must be an
                 integer.',
            'contactable_id.min' => 'The contactable ID must be at least 1.',
            'email.email' => 'The email address must be a valid email.',
            'email.max' => 'The email address may not exceed 255 characters.',
            'phone.max' => 'The phone number may not exceed 255 characters.',
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
            Rule::in(array_keys(
                app(ContactableTypeRegistryService::class)->all()
            )),
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
                $type = $this->input('contactable_type');
                $id = $this->input('contactable_id');

                if (! $type || ! $id) {
                    return;
                }

                $modelClass = app(ContactableTypeRegistryService::class)
                    ->modelClassForKey($type);

                if (! $modelClass) {
                    return;
                }

                if (! $modelClass::whereKey($id)->exists()) {
                    $validator->errors()->add(
                        'contactable_id',
                        'The selected contact owner does not exist.'
                    );
                }
            },
        ];
    }
}
