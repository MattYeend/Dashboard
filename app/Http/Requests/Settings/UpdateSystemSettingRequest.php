<?php

namespace App\Http\Requests\Settings;

use App\Models\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateSystem', Setting::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'maintenance_mode' => $this->maintenanceModeRules(),
            'allow_registrations' => $this->allowRegistrationsRules(),
            'default_pagination' => $this->defaultPaginationRules(),
            'default_locale' => $this->defaultLocaleRules(),
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
            'default_pagination.min' => 'The default pagination must be at least 5.',
            'default_pagination.max' => 'The default pagination may not exceed 100.',
        ];
    }

    /**
     * Get validation rules for the maintenance_mode field.
     *
     * @return array<mixed>
     */
    protected function maintenanceModeRules(): array
    {
        return [
            'required',
            'boolean',
        ];
    }

    /**
     * Get validation rules for the allow_registrations field.
     *
     * @return array<mixed>
     */
    protected function allowRegistrationsRules(): array
    {
        return [
            'required',
            'boolean',
        ];
    }

    /**
     * Get validation rules for the default_pagination field.
     *
     * @return array<mixed>
     */
    protected function defaultPaginationRules(): array
    {
        return [
            'required',
            'integer',
            'min:5',
            'max:100',
        ];
    }

    /**
     * Get validation rules for the default_locale field.
     *
     * @return array<mixed>
     */
    protected function defaultLocaleRules(): array
    {
        return [
            'required',
            'string',
            'max:10',
        ];
    }
}
