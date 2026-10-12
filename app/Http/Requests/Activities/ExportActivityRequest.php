<?php

declare(strict_types=1);

namespace App\Http\Requests\Activities;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExportActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'activityable_type' => [
                'nullable',
                'string',
                'max:50',
                'required_with:activityable_id',
            ],
            'activityable_id' => [
                'nullable',
                'integer',
                'min:1',
                'required_with:activityable_type',
            ],
        ];
    }
}
