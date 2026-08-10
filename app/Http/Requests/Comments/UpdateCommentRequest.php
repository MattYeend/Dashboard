<?php

namespace App\Http\Requests\Comments;

use App\Services\Comments\CommentableTypeRegistryService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('comment'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'commentable_type' => $this->commentableTypeRules(),
            'commentable_id' => $this->commentableIdRules(),
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
            'commentable_type.required' => 'The commentable type is required.',
            'commentable_type.string' => 'The commentable type must be a string.',
            'commentable_id.required' => 'The commentable ID is required.',
            'commentable_id.integer' => 'The commentable ID must be an integer.',
            'commentable_id.min' => 'The commentable ID must be at least 1.',
            'content.required' => 'The comment cannot be empty.',
            'content.string' => 'The comment must be a string.',
            'content.max' => 'The comment may not exceed 2000 characters.',
        ];
    }

    /**
     * Get validation rules for the commentable_type field.
     *
     * @return array<mixed>
     */
    protected function commentableTypeRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            Rule::in(array_keys(
                app(CommentableTypeRegistryService::class)->all()
            )),
        ];
    }

    /**
     * Get validation rules for the commentable_id field.
     *
     * @return array<mixed>
     */
    protected function commentableIdRules(): array
    {
        return [
            'sometimes',
            'required',
            'integer',
            'min:1',
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
            'sometimes',
            'required',
            'string',
            'max:2000',
        ];
    }

    /**
     * Perform additional validation after the standard rules have passed.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $type = $this->input('commentable_type');
                $id = $this->input('commentable_id');

                if (! $type || ! $id) {
                    return;
                }

                $modelClass = app(CommentableTypeRegistryService::class)
                    ->modelClassForKey($type);

                if (! $modelClass) {
                    return;
                }

                if (! $modelClass::whereKey($id)->exists()) {
                    $validator->errors()->add(
                        'commentable_id',
                        'The selected item to comment on does not exist.'
                    );
                }
            },
        ];
    }
}
