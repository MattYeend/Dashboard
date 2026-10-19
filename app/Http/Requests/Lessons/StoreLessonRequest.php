<?php

declare(strict_types=1);

namespace App\Http\Requests\Lessons;

use App\Models\Lesson;
use App\Models\Organisation;
use App\Services\Lessons\VideoUrlService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates input when creating a lesson.
 */
class StoreLessonRequest extends FormRequest
{
    /**
     * Determine whether the user may create lessons.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Lesson::class) ?? false;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'course_id' => $this->courseRules(),
            'title' => $this->titleRules(),
            'summary' => $this->summaryRules(),
            'content' => $this->contentRules(),
            'video_url' => $this->videoUrlRules(),
            'duration_minutes' => $this->durationRules(),
            'is_preview' => $this->previewRules(),
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
            'course_id.required' => 'Please choose a course.',
            'course_id.exists' => 'The selected course could not be found.',
            'title.required' => 'Please enter a lesson title.',
            'video_url.url' => 'The video link must be a valid https address.',
        ];
    }

    /**
     * Rules for the course field, limited to live courses in the current organisation.
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

    /**
     * Rules for the title field.
     *
     * @return array<int, mixed>
     */
    protected function titleRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Rules for the summary field.
     *
     * @return array<int, mixed>
     */
    protected function summaryRules(): array
    {
        return ['nullable', 'string', 'max:500'];
    }

    /**
     * Rules for the content field.
     *
     * @return array<int, mixed>
     */
    protected function contentRules(): array
    {
        return ['nullable', 'string', 'max:200000'];
    }

    /**
     * Rules for the video URL field.
     *
     * @return array<int, mixed>
     */
    protected function videoUrlRules(): array
    {
        return ['nullable', 'url:https', 'max:2048'];
    }

    /**
     * Rules for the duration field.
     *
     * @return array<int, mixed>
     */
    protected function durationRules(): array
    {
        return ['nullable', 'integer', 'min:1', 'max:1440'];
    }

    /**
     * Rules for the preview flag.
     *
     * @return array<int, mixed>
     */
    protected function previewRules(): array
    {
        return ['boolean'];
    }

    /**
     * Rules for the published at field.
     *
     * @return array<int, mixed>
     */
    protected function publishedAtRules(): array
    {
        return ['nullable', 'date'];
    }

    /**
     * Get the validation hooks to run after the base rules.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! app(VideoUrlService::class)->isAllowed($this->input('video_url'))) {
                    $validator->errors()->add('video_url', 'Videos can only be linked from YouTube or Vimeo.');
                }
            },
        ];
    }
}
