<?php

declare(strict_types=1);

namespace App\Http\Requests\EmailTemplates;

use App\Models\EmailTemplate;
use Illuminate\Foundation\Http\FormRequest;

class ImportEmailTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('import', EmailTemplate::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required', 
                'file', 
                'mimes:csv,txt', 
                'max:2048'
            ],
        ];
    }
}