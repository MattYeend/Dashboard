<?php

namespace App\Http\Requests\Systems;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EnableMaintenanceModeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('run maintenance');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'secret' => ['nullable', 'string', 'max:255'],
            'retry' => ['nullable', 'integer', 'min:0'],
            'refresh' => ['nullable', 'integer', 'min:0'],
            'allowed' => ['nullable', 'array'],
            'allowed.*' => ['ip'],
        ];
    }
}
