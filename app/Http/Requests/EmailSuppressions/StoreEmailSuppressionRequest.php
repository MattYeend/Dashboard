<?php

declare(strict_types=1);

namespace App\Http\Requests\EmailSuppressions;

use App\Models\EmailSuppression;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Multitenancy\Models\Tenant;

class StoreEmailSuppressionRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', EmailSuppression::class);
    }

    /**
     * Normalise the email before validation so the uniqueness check
     * matches the value that will be stored.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge([
                'email' => mb_strtolower(trim($this->input('email'))),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Only validated keys reach the services, so campaign, contact,
     * suppressed_at and audit columns can never be mass assigned from a request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => $this->emailRules(),
            'reason' => $this->reasonRules(),
            'notes' => $this->notesRules(),
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
            'email.required' => 'The email address is required.',
            'email.string' => 'The email address must be a string.',
            'email.max' => 'The email address may not exceed 255 characters.',
            'email.email' => 'The email address must be a valid email address.',
            'email.unique' => 'This email address is already on the suppression list. If it was deleted, restore it instead.',
            'reason.required' => 'The reason is required.',
            'reason.in' => 'The selected reason is not available.',
            'notes.string' => 'The notes must be a string.',
            'notes.max' => 'The notes may not exceed 2000 characters.',
        ];
    }

    /**
     * Get validation rules for the email field.
     *
     * Scoped to the current organisation and including soft-deleted rows.
     *
     * @return array<mixed>
     */
    protected function emailRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
            'email:rfc',
            Rule::unique('email_suppressions', 'email')
                ->where('organisation_id', Tenant::current()?->id),
        ];
    }

    /**
     * Get validation rules for the reason field.
     *
     * @return array<mixed>
     */
    protected function reasonRules(): array
    {
        return [
            'required',
            'string',
            Rule::in(EmailSuppression::REASONS),
        ];
    }

    /**
     * Get validation rules for the notes field.
     *
     * @return array<mixed>
     */
    protected function notesRules(): array
    {
        return [
            'nullable',
            'string',
            'max:2000',
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