<?php

declare(strict_types=1);

namespace App\Http\Requests\Courses;

use App\Enums\CourseDifficulty;
use App\Models\Organisation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('course')) ?? false;
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
            'summary' => $this->summaryRules(),
            'description' => $this->descriptionRules(),
            'difficulty' => $this->difficultyRules(),
            'estimated_duration_minutes' => $this->durationRules(),
            'instructor_id' => $this->instructorRules(),
            'published_at' => $this->publishedAtRules(),
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a course title.',
            'difficulty.required' => 'Please choose a difficulty.',
            'instructor_id.exists' => 'The selected instructor is not a member of this organisation.',
        ];
    }

    /**
     * Rules for the title field.
     *
     * @return array<int, mixed>
     */
    protected function titleRules(): array
    {
        return [
            'sometimes', 
            'required', 
            'string', 
            'max:255'
            ];
    }

    /**
     * Rules for the summary field.
     *
     * @return array<int, mixed>
     */
    protected function summaryRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:500'
        ];
    }

    /**
     * Rules for the description field.
     *
     * @return array<int, mixed>
     */
    protected function descriptionRules(): array
    {
        return [
            'sometimes', 
            'nullable', 
            'string', 
            'max:65000'
        ];
    }

    /**
     * Rules for the difficulty field.
     *
     * @return array<int, mixed>
     */
    protected function difficultyRules(): array
    {
        return [
            'sometimes', 
            'required', 
            Rule::enum(CourseDifficulty::class)
        ];
    }

    /**
     * Rules for the estimated duration field.
     *
     * @return array<int, mixed>
     */
    protected function durationRules(): array
    {
        return [
            'sometimes', 
            'nullable', 
            'integer', 
            'min:1', 
            'max:10080'
        ];
    }

    /**
     * Rules for the instructor field, limited to members of the current organisation.
     *
     * @return array<int, mixed>
     */
    protected function instructorRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            Rule::exists('organisation_user', 'user_id')
                ->where('organisation_id', Organisation::current()?->id),
        ];
    }

    /**
     * Rules for the published at field.
     *
     * @return array<int, mixed>
     */
    protected function publishedAtRules(): array
    {
        return [
            'sometimes', 
            'nullable', 
            'date'
        ];
    }
}
