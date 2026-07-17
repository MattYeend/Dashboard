<?php

namespace App\Http\Requests\ApiTokens;

use App\Enums\TokenAbility;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;

class StoreApiTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PersonalAccessToken::class);
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
            'expires_at' => $this->expiresAtRules(),
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
            'name.required' => 'The token name is required.',
            'abilities.required' => 'Select at least one ability for this token.',
            'abilities.*.in' => 'One of the selected abilities is invalid.',
            'expires_at.after' => 'The expiry date must be in the future.',
        ];
    }

    /**
     * Get validation rules for the name field.
     *
     * @return array<mixed>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get validation rules for the abilities field.
     *
     * @return array<mixed>
     */
    protected function abilitiesRules(): array
    {
        return ['required', 'array', 'min:1'];
    }

    /**
     * Get validation rules for each ability entry.
     *
     * @return array<mixed>
     */
    protected function abilityRules(): array
    {
        return ['string', Rule::in(TokenAbility::values())];
    }

    /**
     * Get validation rules for the expires_at field.
     *
     * @return array<mixed>
     */
    protected function expiresAtRules(): array
    {
        return ['nullable', 'date', 'after:now'];
    }
}
