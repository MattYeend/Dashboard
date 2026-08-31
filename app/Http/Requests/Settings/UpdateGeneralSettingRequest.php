<?php

namespace App\Http\Requests\Settings;

use App\Models\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateGeneral', Setting::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'site_name' => $this->siteNameRules(),
            'support_email' => $this->supportEmailRules(),
            'timezone' => $this->timezoneRules(),
            'date_format' => $this->dateFormatRules(),
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
            'site_name.required' => 'The site name is required.',
            'support_email.required' => 'The support email is required.',
            'support_email.email' => 'The support email must be a valid email address.',
            'timezone.timezone' => 'The timezone must be a valid timezone identifier.',
            'date_format.in' => 'The date format must be one of the supported formats.',
        ];
    }

    /**
     * Get validation rules for the site_name field.
     *
     * @return array<mixed>
     */
    protected function siteNameRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the support_email field.
     *
     * @return array<mixed>
     */
    protected function supportEmailRules(): array
    {
        return [
            'required',
            'email',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the timezone field.
     *
     * @return array<mixed>
     */
    protected function timezoneRules(): array
    {
        return [
            'required',
            'string',
            'timezone',
        ];
    }

    /**
     * Get validation rules for the date_format field.
     *
     * @return array<mixed>
     */
    protected function dateFormatRules(): array
    {
        return [
            'required',
            'string',
            'in:d/m/Y,Y-m-d,m/d/Y',
        ];
    }
}
