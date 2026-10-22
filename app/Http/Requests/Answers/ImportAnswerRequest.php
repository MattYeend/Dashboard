<?php

declare(strict_types=1);

namespace App\Http\Requests\Answers;

use App\Models\Answer;
use Illuminate\Foundation\Http\FormRequest;

class ImportAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     *
     * Importing writes the correctness flags, so the user must also be
     * allowed to see them.
     */
    public function authorize(): bool
    {
        return ($this->user()?->can('import', Answer::class) ?? false)
            && $this->user()->can('viewCorrect', Answer::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:5120', // 5MB
            ],
        ];
    }
}
