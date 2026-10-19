<?php

declare(strict_types=1);
 
namespace Database\Seeders;
 
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Organisation;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organisation = Organisation::query()->oldest('id')->firstOrFail();
        $organisation->makeCurrent();
 
        $lessonsByCourse = [
            'introduction-to-laravel' => [
                ['Installing Laravel', 'installing-laravel', 'Create a new project with Composer and run it locally.', 15, true],
                ['Routing and controllers', 'routing-and-controllers', 'Map URLs to controller actions and use route model binding.', 30, false],
                ['Eloquent models and migrations', 'eloquent-models-and-migrations', 'Define tables with migrations and query them with Eloquent.', 40, false],
                ['Blade templates', 'blade-templates', 'Build layouts and components with Blade.', 25, false],
                ['Validation and form requests', 'validation-and-form-requests', 'Validate input with form request classes.', 25, false],
            ],
            'building-interfaces-with-vuejs' => [
                ['Reactivity with ref and computed', 'reactivity-with-ref-and-computed', 'Understand reactive state in the Composition API.', 30, true],
                ['Components, props and emits', 'components-props-and-emits', 'Split an interface into small reusable components.', 35, false],
                ['Forms with defineModel', 'forms-with-definemodel', 'Bind form fields to parent state with defineModel.', 30, false],
                ['Inertia.js pages and forms', 'inertiajs-pages-and-forms', 'Connect Vue pages to Laravel controllers with Inertia.', 40, false],
            ],
            'mysql-query-design-and-indexing' => [
                ['Reading an EXPLAIN plan', 'reading-an-explain-plan', 'Use EXPLAIN to see how MySQL runs a query.', 25, true],
                ['Choosing indexes', 'choosing-indexes', 'Single column, composite and covering indexes.', 35, false],
                ['Avoiding N+1 queries', 'avoiding-n-plus-1-queries', 'Eager load relationships in Eloquent.', 20, false],
            ],
            'information-security-fundamentals' => [
                ['What ISO 27001 is', 'what-iso-27001-is', 'An overview of the standard and Annex A controls.', 20, true],
                ['Access control and least privilege', 'access-control-and-least-privilege', 'Roles, permissions and tenant isolation.', 25, false],
                ['Logging and audit trails', 'logging-and-audit-trails', 'Record who did what, and when.', 20, false],
            ],
        ];
 
        foreach ($lessonsByCourse as $courseSlug => $lessons) {
            $course = Course::query()->where('slug', $courseSlug)->first();
 
            if ($course === null) {
                continue;
            }
 
            foreach ($lessons as $index => [$title, $slug, $summary, $minutes, $isPreview]) {
                Lesson::updateOrCreate(
                    ['course_id' => $course->id, 'slug' => $slug],
                    [
                        'organisation_id' => $organisation->id,
                        'title' => $title,
                        'summary' => $summary,
                        'content' => '<p>' . $summary . '</p>',
                        'position' => $index + 1,
                        'duration_minutes' => $minutes,
                        'is_preview' => $isPreview,
                        'published_at' => now()->subDay(),
                    ],
                );
            }
        }
    }
}