<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CourseDifficulty;
use App\Models\Course;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organisation = Organisation::query()->oldest('id')->firstOrFail();
        $organisation->makeCurrent();

        $instructorId = User::query()->oldest('id')->value('id');

        $courses = [
            [
                'title' => 'Introduction to Laravel',
                'slug' => 'introduction-to-laravel',
                'summary' => 'Routing, controllers, Eloquent and Blade for new Laravel developers.',
                'difficulty' => CourseDifficulty::Beginner,
                'estimated_duration_minutes' => 240,
            ],
            [
                'title' => 'Building Interfaces with Vue.js',
                'slug' => 'building-interfaces-with-vuejs',
                'summary' => 'Components, reactivity and forms with the Vue.js Composition API.',
                'difficulty' => CourseDifficulty::Intermediate,
                'estimated_duration_minutes' => 300,
            ],
            [
                'title' => 'MySQL Query Design and Indexing',
                'slug' => 'mysql-query-design-and-indexing',
                'summary' => 'Write efficient queries and choose the right indexes.',
                'difficulty' => CourseDifficulty::Intermediate,
                'estimated_duration_minutes' => 180,
            ],
            [
                'title' => 'Information Security Fundamentals',
                'slug' => 'information-security-fundamentals',
                'summary' => 'An introduction to ISO 27001 controls for developers.',
                'difficulty' => CourseDifficulty::Beginner,
                'estimated_duration_minutes' => 150,
            ],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(
                ['slug' => $course['slug']],
                $course + [
                    'instructor_id' => $instructorId,
                    'published_at' => now()->subDay(),
                ],
            );
        }
    }
}
