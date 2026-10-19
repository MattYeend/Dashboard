<?php

declare(strict_types=1);

namespace App\Http\Requests\Lessons;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Organisation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a lesson reorder request.
 */
class ReorderLessonsRequest extends FormRequest
{
    /**
     * Determine whether the user may reorder lessons in the course.
     */
    public function authorize(): bool
    {
        $course = $this->course();

        return $course !== null
            && ($this->user()?->can('reorder', [Lesson::class, $course]) ?? false);
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'course_id' => [
                'required',
                'integer',
                Rule::exists('courses', 'id')
                    ->where('organisation_id', Organisation::current()?->id)
                    ->whereNull('deleted_at'),
            ],
            'lesson_ids' => ['required', 'array', 'min:1', 'max:500'],
            'lesson_ids.*' => ['integer', 'distinct'],
        ];
    }

    /**
     * Resolve the course being reordered, scoped to the current organisation.
     */
    public function course(): ?Course
    {
        return Course::query()->find($this->integer('course_id'));
    }
}
