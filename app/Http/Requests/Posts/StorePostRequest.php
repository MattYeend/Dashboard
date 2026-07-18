<?php

namespace App\Http\Requests\Posts;

use App\Models\Post;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
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
}
