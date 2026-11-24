<?php

declare(strict_types=1);

namespace App\Http\Requests\CampaignRecipients;

use App\Models\CampaignRecipient;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRecipientRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', CampaignRecipient::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Only validated keys reach the services, so status, token, audit columns
     * and workflow timestamps can never be mass assigned from a request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
            'meta.array' => 'The meta data must be an array.',
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