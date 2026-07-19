<?php

namespace App\Http\Requests\Comments;

use App\Models\Comment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [Comment::class, $this->route('post')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => $this->contentRules(),
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
            'content.required' => 'The comment cannot be empty.',
            'content.string' => 'The comment must be a string.',
            'content.max' => 'The comment may not exceed 2000 characters.',
        ];
    }

    /**
     * Get validation rules for the content field.
     *
     * @return array<mixed>
     */
    protected function contentRules(): array
    {
        return [
            'required',
            'string',
            'max:2000',
        ];
    }
}
