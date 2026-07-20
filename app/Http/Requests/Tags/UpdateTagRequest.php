<?php

namespace App\Http\Requests\Tags;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('tag'));
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
            'slug' => $this->slugRules(),
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
            'name.required' => 'The tag name is required.',
            'name.string' => 'The tag name must be a string.',
            'name.max' => 'The tag name may not exceed 255 characters.',
            'slug.unique' => 'This slug is already in use.',
        ];
    }

    /**
     * Get validation rules for the name field.
     *
     * @return array<mixed>
     */
    protected function nameRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the slug field.
     *
     * @return array<mixed>
     */
    protected function slugRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:255',
            Rule::unique('tags', 'slug')->ignore($this->route('tag')->id),
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
            'sometimes',
            'nullable',
            'array',
        ];
    }
}
