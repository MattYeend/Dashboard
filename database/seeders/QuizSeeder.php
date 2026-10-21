<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Organisation;
use App\Models\Question;
use App\Models\Quiz;
use App\Services\Quizzes\QuestionAttachmentService;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organisation = Organisation::query()->oldest('id')->firstOrFail();
        $organisation->makeCurrent();

        $attachments = app(QuestionAttachmentService::class);

        $quizzes = [
            [
                'course' => 'introduction-to-laravel',
                'lesson' => 'routing-and-controllers',
                'title' => 'Laravel routing check',
                'slug' => 'laravel-routing-check',
                'prompts' => [
                    'Which file defines the web routes in a default Laravel application?',
                    'Route model binding can resolve an Eloquent model from a route parameter.',
                    'Which of these are valid HTTP verbs for Laravel route definitions?',
                ],
            ],
            [
                'course' => 'building-interfaces-with-vuejs',
                'lesson' => 'forms-with-definemodel',
                'title' => 'Vue forms check',
                'slug' => 'vue-forms-check',
                'prompts' => [
                    'Which macro lets a child component expose a two-way bound value?',
                    'A ref value is read in a script block with .value.',
                ],
            ],
            [
                'course' => 'mysql-query-design-and-indexing',
                'lesson' => 'choosing-indexes',
                'title' => 'MySQL indexing check',
                'slug' => 'mysql-indexing-check',
                'prompts' => [
                    'Which statement shows how MySQL plans to run a query?',
                    'Adding an index always makes writes faster.',
                ],
            ],
        ];

        foreach ($quizzes as $data) {
            $course = Course::query()->where('slug', $data['course'])->first();

            if ($course === null) {
                continue;
            }

            $lessonId = Lesson::query()
                ->where('course_id', $course->id)
                ->where('slug', $data['lesson'])
                ->value('id');

            $quiz = Quiz::updateOrCreate(
                ['course_id' => $course->id, 'slug' => $data['slug']],
                [
                    'organisation_id' => $organisation->id,
                    'lesson_id' => $lessonId,
                    'title' => $data['title'],
                    'description' => '<p>A short check of this lesson.</p>',
                    'pass_mark_percentage' => 70,
                    'shuffle_questions' => false,
                    'show_correct_answers' => true,
                    'published_at' => now()->subDay(),
                ],
            );

            $questionIds = Question::query()
                ->whereIn('prompt', $data['prompts'])
                ->pluck('id', 'prompt');

            $selections = [];

            foreach ($data['prompts'] as $prompt) {
                if (isset($questionIds[$prompt])) {
                    $selections[] = ['question_id' => $questionIds[$prompt], 'points' => null];
                }
            }

            $attachments->sync($quiz, $selections);
        }
    }
}
