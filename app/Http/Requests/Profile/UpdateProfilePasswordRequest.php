<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateProfilePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('changeOwnPassword', $this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => $this->currentPasswordRules(),
            'password' => $this->passwordRules(),
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.current_password' => 'The current password is incorrect.',
            'password.different' => 'The new password must be different from the current password.',
        ];
    }

    /**
     * Rules for the current password field.
     *
     * @return array<int, string>
     */
    protected function currentPasswordRules(): array
    {
        return [
            'required', 
            'string', 
            'current_password'
        ];
    }

    /**
     * Rules for the new password field.
     *
     * @return array<int, mixed>
     */
    protected function passwordRules(): array
    {
        return [
            'required', 
            'string', 
            Password::defaults(), 
            'confirmed', 
            'different:current_password'
        ];
    }
}
