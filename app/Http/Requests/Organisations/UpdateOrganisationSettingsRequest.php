<?php

namespace App\Http\Requests\Organisations;

use App\Models\Organisation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganisationSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manageSettings', $this->route('organisation'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'logo_attachment_id' => $this->logoAttachmentIdRules(),
            'accent_colour' => $this->accentColourRules(),
            'default_timezone' => $this->defaultTimezoneRules(),
            'default_locale' => $this->defaultLocaleRules(),
            'notification_preferences' => $this->notificationPreferencesRules(),
            'notification_preferences.email_notifications' => $this->notificationPreferencesEmailNotificationsRules(),
            'notification_preferences.weekly_digest' => $this->notificationPreferencesWeeklyDigestRules(),
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
            'accent_colour.regex' => 'The accent colour must be a valid hex colour code, e.g. #4F46E5.',
            'default_timezone.timezone' => 'The default timezone must be a valid timezone identifier.',
            'logo_attachment_id.exists' => 'That logo could not be found for this organisation.',
        ];
    }

    /**
     * Get validation rules for the logo attachment reference.
     *
     * Scoped to this organisation's own attachments - matched on both
     * organisation_id and the polymorphic attachable columns - and
     * excludes soft-deleted attachments.
     *
     * @return array<mixed>
     */
    protected function logoAttachmentIdRules(): array
    {
        $organisation = $this->route('organisation');

        return [
            'sometimes',
            'nullable',
            'integer',
            Rule::exists('attachments', 'id')->where(function ($query) use ($organisation) {
                $query->where('organisation_id', $organisation->id)
                    ->where('attachable_type', Organisation::class)
                    ->where('attachable_id', $organisation->id)
                    ->whereNull('deleted_at');
            }),
        ];
    }

    /**
     * Get validation rules for the accent colour field.
     *
     * @return array<mixed>
     */
    protected function accentColourRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }

    /**
     * Get validation rules for the default timezone field.
     *
     * @return array<mixed>
     */
    protected function defaultTimezoneRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'timezone',
        ];
    }

    /**
     * Get validation rules for the default locale field.
     *
     * @return array<mixed>
     */
    protected function defaultLocaleRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            Rule::in(['en', 'en-GB']),
        ];
    }

    /**
     * Get validation rules for the notification preferences field.
     *
     * @return array<mixed>
     */
    protected function notificationPreferencesRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'array:email_notifications,weekly_digest',
        ];
    }

    /**
     * Get validation rules for the notification preferences email notifications field.
     *
     * @return array<mixed>
     */
    protected function notificationPreferencesEmailNotificationsRules(): array
    {
        return [
            'sometimes',
            'boolean',
        ];
    }

    /**
     * Get validation rules for the notification preferences weekly digest field.
     *
     * @return array<mixed>
     */
    protected function notificationPreferencesWeeklyDigestRules(): array
    {
        return [
            'sometimes',
            'boolean',
        ];
    }
}
