<?php

namespace App\Http\Requests\Settings;

use App\Models\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSecuritySettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateSecurity', Setting::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'two_factor_required' => $this->twoFactorRequiredRules(),
            'session_timeout_minutes' => $this->sessionTimeoutMinutesRules(),
            'max_login_attempts' => $this->maxLoginAttemptsRules(),
            'password_expiry_days' => $this->passwordExpiryDaysRules(),
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
            'session_timeout_minutes.min' => 'The session timeout must be at least 5 minutes.',
            'session_timeout_minutes.max' => 'The session timeout may not exceed 1440 minutes (24 hours).',
            'max_login_attempts.min' => 'The maximum login attempts must be at least 3.',
        ];
    }

    /**
     * Get validation rules for the two_factor_required field.
     *
     * @return array<mixed>
     */
    protected function twoFactorRequiredRules(): array
    {
        return [
            'required',
            'boolean',
        ];
    }

    /**
     * Get validation rules for the session_timeout_minutes field.
     *
     * @return array<mixed>
     */
    protected function sessionTimeoutMinutesRules(): array
    {
        return [
            'required',
            'integer',
            'min:5',
            'max:1440',
        ];
    }

    /**
     * Get validation rules for the max_login_attempts field.
     *
     * @return array<mixed>
     */
    protected function maxLoginAttemptsRules(): array
    {
        return [
            'required',
            'integer',
            'min:3',
            'max:20',
        ];
    }

    /**
     * Get validation rules for the password_expiry_days field.
     *
     * @return array<mixed>
     */
    protected function passwordExpiryDaysRules(): array
    {
        return [
            'nullable',
            'integer',
            'min:30',
            'max:365',
        ];
    }
}
