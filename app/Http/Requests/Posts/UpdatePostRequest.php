<?php

namespace App\Http\Requests\Posts;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('post'));
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
            'image' => $this->imageRules(),
            'meta' => $this->metaRules(),
            'category_ids' => $this->categoryIdsRules(),
            'category_ids.*' => $this->categoryIdRules(),
            'tag_ids' => $this->tagIdsRules(),
            'tag_ids.*' => $this->tagIdRules(),
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
            'title.required' => 'The post title is required.',
            'title.string' => 'The post title must be a string.',
            'title.max' => 'The post title may not exceed 255 characters.',
            'description.required' => 'The post description is required.',
            'description.string' => 'The post description must be a string.',
            'image.image' => 'The image must be a valid image file.',
            'image.max' => 'The image may not be larger than 5MB.',
            'category_ids.array' => 'Categories must be provided as a list.',
            'category_ids.*.exists' => 'One or more selected categories are invalid.',
            'tag_ids.array' => 'Tags must be provided as a list.',
            'tag_ids.*.exists' => 'One or more selected tags are invalid.',
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
            'sometimes',
            'required',
            'string',
        ];
    }

    /**
     * Get validation rules for the image field.
     *
     * @return array<mixed>
     */
    protected function imageRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'image',
            'max:5120',
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

    /**
     * Get validation rules for the category_ids field.
     *
     * @return array<mixed>
     */
    protected function categoryIdsRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'array',
        ];
    }

    /**
     * Get validation rules for each category_ids entry.
     *
     * @return array<mixed>
     */
    protected function categoryIdRules(): array
    {
        return [
            'integer',
            'exists:categories,id',
        ];
    }

    /**
     * Get validation rules for the tag_ids field.
     *
     * @return array<mixed>
     */
    protected function tagIdsRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'array',
        ];
    }

    /**
     * Get validation rules for each tag_ids entry.
     *
     * @return array<mixed>
     */
    protected function tagIdRules(): array
    {
        return [
            'integer',
            'exists:tags,id',
        ];
    }
}
