<?php

namespace App\Http\Requests\Addresses;

use App\Services\Addresses\AddressableTypeRegistryService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('address'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'addressable_type' => $this->addressableTypeRules(),
            'addressable_id' => $this->addressableIdRules(),
            'address_line_one' => $this->addressLineOneRules(),
            'address_line_two' => $this->addressLineTwoRules(),
            'town' => $this->townRules(),
            'city' => $this->cityRules(),
            'county' => $this->countyRules(),
            'postcode' => $this->postcodeRules(),
            'country' => $this->countryRules(),
            'is_primary' => $this->isPrimaryRules(),
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
            'addressable_type.required' => 'The addressable type is required.',
            'addressable_type.string' => 'The addressable type must be a string.',
            'addressable_id.required' => 'The addressable ID is required.',
            'addressable_id.integer' => 'The addressable ID must be an integer.',
            'addressable_id.min' => 'The addressable ID must be at least 1.',
            'address_line_one.required' => 'The first line of the address is required.',
            'address_line_one.max' => 'The first line of the address may not exceed 255 characters.',
            'address_line_two.max' => 'The second line of the address may not exceed 255 characters.',
            'town.max' => 'The town may not exceed 255 characters.',
            'city.required' => 'The city is required.',
            'city.max' => 'The city may not exceed 255 characters.',
            'county.max' => 'The county may not exceed 255 characters.',
            'postcode.max' => 'The postcode may not exceed 255 characters.',
            'country.required' => 'The country is required.',
            'country.max' => 'The country may not exceed 255 characters.',
            'is_primary.boolean' => 'The primary flag must be true or false.',
        ];
    }

    /**
     * Get validation rules for the addressable_type field.
     *
     * @return array<mixed>
     */
    protected function addressableTypeRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            Rule::in(array_keys(
                app(AddressableTypeRegistryService::class)->all()
            )),
        ];
    }

    /**
     * Get validation rules for the addressable_id field.
     *
     * @return array<mixed>
     */
    protected function addressableIdRules(): array
    {
        return [
            'sometimes',
            'required',
            'integer',
            'min:1',
        ];
    }

    /**
     * Get validation rules for the address_line_one field.
     *
     * @return array<mixed>
     */
    protected function addressLineOneRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the address_line_two field.
     *
     * @return array<mixed>
     */
    protected function addressLineTwoRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the town field.
     *
     * @return array<mixed>
     */
    protected function townRules(): array
    {
        return [
            'sometimes',
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
            'sometimes',
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the county field.
     *
     * @return array<mixed>
     */
    protected function countyRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the postcode field.
     *
     * @return array<mixed>
     */
    protected function postcodeRules(): array
    {
        return [
            'sometimes',
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
            'sometimes',
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the is_primary field.
     *
     * @return array<mixed>
     */
    protected function isPrimaryRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'boolean',
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
     * Perform additional validation after the standard rules have passed.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $type = $this->input('addressable_type');
                $id = $this->input('addressable_id');

                if (! $type || ! $id) {
                    return;
                }

                $modelClass = app(AddressableTypeRegistryService::class)
                    ->modelClassForKey($type);

                if (! $modelClass) {
                    return;
                }

                if (! $modelClass::whereKey($id)->exists()) {
                    $validator->errors()->add(
                        'addressable_id',
                        'The selected address owner does not exist.'
                    );
                }
            },
        ];
    }
}
