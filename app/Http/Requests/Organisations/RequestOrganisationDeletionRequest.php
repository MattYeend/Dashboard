<?php

namespace App\Http\Requests\Organisations;

use App\Models\Organisation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RequestOrganisationDeletionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('requestDeletion', $this->route('organisation'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->confirmationNameRules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmation_name.in' => 'The organisation name you typed does not match. Please type it exactly to confirm deletion.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function confirmationNameRules(): array
    {
        /** @var Organisation $organisation */
        $organisation = $this->route('organisation');

        return [
            'confirmation_name' => [
                'required', 
                'string', 
                'in:'.$organisation->name
            ],
        ];
    }
}
