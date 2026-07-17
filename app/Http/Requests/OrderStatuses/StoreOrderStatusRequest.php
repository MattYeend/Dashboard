<?php

namespace App\Http\Requests\OrderStatuses;

use App\Models\OrderStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', OrderStatus::class);
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
            'description' => $this->descriptionRules(),
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
            'title.required' => 'The title is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not exceed 255 characters.',
            'background_colour.regex' => 'The background colour must be a valid hex colour (e.g. #ffffff).',
            'background_colour.max' => 'The background colour may not exceed 7 characters.',
            'text_colour.regex' => 'The text colour must be a valid hex colour (e.g. #000000).',
            'text_colour.max' => 'The text colour may not exceed 7 characters.',
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
     * Get validation rules for the background_colour field.
     *
     * @return array<mixed>
     */
    protected function backgroundColourRules(): array
    {
        return [
            'nullable',
            'string',
            'max:7',
            'regex:/^#[0-9A-Fa-f]{6}$/',
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
            'nullable',
            'string',
            'max:7',
            'regex:/^#[0-9A-Fa-f]{6}$/',
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
