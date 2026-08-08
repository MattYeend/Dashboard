<?php

namespace App\Http\Requests\TicketPriorities;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketPriorityRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ticket_priority'));
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
            'level' => $this->levelRules(),
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
            'title.unique' => 'A ticket priority with this title already exists.',
            'level.integer' => 'The level must be a whole number.',
            'level.min' => 'The level must be at least 1.',
            'level.max' => 'The level may not exceed 4.',
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
            'sometimes',
            'required',
            'string',
            'max:255',
            Rule::unique('ticket_priorities', 'title')->ignore($this->route('ticket_priority')),
        ];
    }

    /**
     * Get validation rules for the level field.
     *
     * @return array<mixed>
     */
    protected function levelRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'min:1',
            'max:4',
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
            'sometimes',
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
            'sometimes',
            'nullable',
            'array',
        ];
    }
}
