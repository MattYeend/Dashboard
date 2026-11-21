<?php

declare(strict_types=1);

namespace App\Http\Requests\EmailTemplates;

use App\Models\EmailTemplate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmailTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', EmailTemplate::class);
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
            'subject' => $this->subjectRules(),
            'preheader' => $this->preheaderRules(),
            'body_html' => $this->bodyHtmlRules(),
            'body_text' => $this->bodyTextRules(),
            'is_active' => $this->isActiveRules(),
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
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not exceed 255 characters.',
            'subject.required' => 'The subject is required.',
            'subject.string' => 'The subject must be a string.',
            'subject.max' => 'The subject may not exceed 255 characters.',
            'preheader.string' => 'The preheader must be a string.',
            'preheader.max' => 'The preheader may not exceed 255 characters.',
            'body_html.required' => 'The HTML body is required.',
            'body_html.string' => 'The HTML body must be a string.',
            'body_html.max' => 'The HTML body may not exceed 200,000 characters.',
            'body_text.string' => 'The plain text body must be a string.',
            'body_text.max' => 'The plain text body may not exceed 20,000 characters.',
            'is_active.boolean' => 'The active status must be true or false.',
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
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the subject field.
     *
     * @return array<mixed>
     */
    protected function subjectRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the preheader field.
     *
     * @return array<mixed>
     */
    protected function preheaderRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the body_html field.
     *
     * @return array<mixed>
     */
    protected function bodyHtmlRules(): array
    {
        return [
            'required',
            'string',
            'max:200000',
        ];
    }

    /**
     * Get validation rules for the body_text field.
     *
     * @return array<mixed>
     */
    protected function bodyTextRules(): array
    {
        return [
            'nullable',
            'string',
            'max:20000',
        ];
    }

    /**
     * Get validation rules for the is_active field.
     *
     * @return array<mixed>
     */
    protected function isActiveRules(): array
    {
        return [
            'sometimes',
            'boolean',
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
