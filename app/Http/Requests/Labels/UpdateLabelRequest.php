<?php

namespace App\Http\Requests\Labels;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLabelRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('label'));
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
            'background_colour' => $this->backgroundColourRules(),
            'text_colour' => $this->textColourRules(),
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
            'name.required' => 'The name is required.',
            'name.max' => 'The name may not exceed 255 characters.',
            'slug.unique' => 'That slug is already in use.',
            'background_colour.regex' => 'The background colour must be a valid hex code, e.g. #6b7280.',
            'text_colour.regex' => 'The text colour must be a valid hex code, e.g. #ffffff.',
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
            'required',
            'string',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'max:255',
            Rule::unique('labels', 'slug')->ignore($this->route('label')),
        ];
    }

    /**
     * Get validation rules for the background_colour field.
     *
     * @return array<mixed>
     */
    protected function backgroundColourRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'regex:/^#[0-9a-fA-F]{6}$/',
        ];
    }

    /**
     * Get validation rules for the text_colour field.
     *
     * @return array<mixed>
     */
    protected function textColourRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'regex:/^#[0-9a-fA-F]{6}$/',
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
