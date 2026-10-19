<?php
 
declare(strict_types=1);
 
namespace App\Http\Requests\Lessons;
 
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Organisation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
 
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
        $course = Course::query()->find($this->input('course_id'));
 
        return $course !== null && ($this->user()?->can('update', $course) ?? false);
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
     * Ensure the ids are exactly the lessons in the course.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }
 
                $existing = Lesson::query()
                    ->where('course_id', $this->integer('course_id'))
                    ->pluck('id')
                    ->sort()
                    ->values()
                    ->all();
 
                $submitted = collect($this->input('lesson_ids'))->map(fn ($id) => (int) $id)->sort()->values()->all();
 
                if ($existing !== $submitted) {
                    $validator->errors()->add('lesson_ids', 'The lesson order must include every lesson in the course.');
                }
            },
        ];
    }
}
