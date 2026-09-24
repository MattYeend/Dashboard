<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;

class StoreProfileTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', PersonalAccessToken::class);
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
            'abilities' => $this->abilitiesRules(),
            'abilities.*' => $this->abilityRules(),
            'expires_in_days' => $this->expiryRules(),
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
            'abilities.required' => 'Select at least one ability.',
            'abilities.*.in' => 'One of the selected abilities is not allowed.',
        ];
    }

    /**
     * Rules for the token name.
     *
     * @return array<int, string>
     */
    protected function nameRules(): array
    {
        return [
            'required',
            'string',
            'max:100',
        ];
    }

    /**
     * Rules for the abilities list. At least one ability is required.
     *
     * @return array<int, string>
     */
    protected function abilitiesRules(): array
    {
        return [
            'required',
            'array',
            'min:1',
        ];
    }

    /**
     * Rules for each ability. Only allow-listed abilities are accepted.
     *
     * @return array<int, mixed>
     */
    protected function abilityRules(): array
    {
        return [
            'string',
            Rule::in(config('profile.token_abilities', [])),
        ];
    }

    /**
     * Rules for the expiry. Non-expiring tokens are not offered.
     *
     * @return array<int, mixed>
     */
    protected function expiryRules(): array
    {
        return [
            'required',
            'integer',
            Rule::in(config('profile.token_lifetimes_days', [])),
        ];
    }
}
