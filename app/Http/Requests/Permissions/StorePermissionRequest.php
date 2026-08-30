<?php

namespace App\Http\Requests\Permissions;

use App\Models\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Permission::class);
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
            'guard_name' => $this->guardNameRules(),
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
            'name.required' => 'The permission name is required.',
            'name.string' => 'The permission name must be a string.',
            'name.max' => 'The permission name may not exceed 150 characters.',
            'name.unique' => 'This permission name is already in use.',
            'guard_name.required' => 'The guard name is required.',
            'guard_name.string' => 'The guard name must be a string.',
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
            'max:150',
            'unique:permissions,name',
        ];
    }

    /**
     * Get validation rules for the guard_name field.
     *
     * @return array<mixed>
     */
    protected function guardNameRules(): array
    {
        return [
            'required',
            'string',
            'max:50',
        ];
    }
}
