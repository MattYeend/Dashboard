<?php

namespace App\Http\Requests\Permissions;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignPermissionRolesRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('assign', $this->route('permission'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_ids' => $this->roleIdsRules(),
            'role_ids.*' => $this->roleIdRules(),
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
            'role_ids.present' => 'The role_ids field must be present, even if empty.',
            'role_ids.array' => 'The role_ids field must be an array.',
            'role_ids.*.exists' => 'One or more selected roles do not exist.',
        ];
    }

    /**
     * Get validation rules for the role_ids field.
     *
     * @return array<mixed>
     */
    protected function roleIdsRules(): array
    {
        return [
            'present',
            'array',
        ];
    }

    /**
     * Get validation rules for each entry in role_ids.
     *
     * @return array<mixed>
     */
    protected function roleIdRules(): array
    {
        return [
            'integer',
            'exists:roles,id',
        ];
    }
}
