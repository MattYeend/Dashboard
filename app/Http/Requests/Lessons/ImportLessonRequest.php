<?php

declare(strict_types=1);

namespace App\Http\Requests\Lessons;

use App\Models\Lesson;
use App\Models\Organisation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates input when importing lessons from a CSV file.
 */
class ImportLessonRequest extends FormRequest
{
    /**
     * Determine whether the user may import lessons.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('import', Lesson::class) ?? false;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'file' => $this->fileRules(),
            'course_id' => $this->courseRules(),
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
            'file.required' => 'Please choose a CSV file to import.',
            'file.mimes' => 'The import file must be a CSV file.',
            'file.max' => 'The import file must not be larger than 5MB.',
            'course_id.required' => 'Please choose the course to import into.',
            'course_id.exists' => 'The selected course could not be found.',
        ];
    }

    /**
     * Rules for the uploaded file.
     *
     * @return array<int, mixed>
     */
    protected function fileRules(): array
    {
        return [
            'required',
            'file',
            'mimes:csv,txt',
            'max:5120', // 5MB
        ];
    }

    /**
     * Rules for the target course, limited to live courses in the current organisation.
     *
     * @return array<int, mixed>
     */
    protected function courseRules(): array
    {
        return [
            'required',
            'integer',
            Rule::exists('courses', 'id')
                ->where('organisation_id', Organisation::current()?->id)
                ->whereNull('deleted_at'),
        ];
    }
}
