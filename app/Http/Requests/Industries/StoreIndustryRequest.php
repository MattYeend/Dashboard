<?php

namespace App\Http\Requests\Industries;

use App\Models\Industry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIndustryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Industry::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => $this->titleRules(),
            'code' => $this->codeRules(),
            'description' => $this->descriptionRules(),
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
            'title.required' => 'The title is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not exceed 255 characters.',
            'code.string' => 'The code must be a string.',
            'code.max' => 'The code may not exceed 255 characters.',
            'code.unique' => 'This code is already in use.',
        ];
    }

    /**
     * Get validation rules for the title field.
     *
     * @return array<mixed>
     */
    protected function titleRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the code field.
     *
     * @return array<mixed>
     */
    protected function codeRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
            'unique:industries,code',
        ];
    }

    /**
     * Get validation rules for the description field.
     *
     * @return array<mixed>
     */
    protected function descriptionRules(): array
    {
        return [
            'nullable',
            'string',
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
