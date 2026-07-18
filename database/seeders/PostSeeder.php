<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->first();

        $posts = [
            [
                'title' => 'Getting Started with Laravel Service Classes',
                'description' => '<p>Service classes help keep controllers thin by moving business logic into single-responsibility units such as CreatorService, UpdaterService and QueryService.</p><p>This pattern makes testing easier and keeps each class focused on one job.</p>',
            ],
            [
                'title' => 'Building Reusable Vue Components',
                'description' => '<p>Splitting large forms into smaller components, such as a basic details form and a role details form, keeps templates readable and encourages reuse between Create and Edit pages.</p>',
            ],
            [
                'title' => 'Indexing Strategies for MySQL',
                'description' => '<p>Composite indexes can significantly improve query performance when filtering and sorting on multiple columns, but they need to be ordered to match the most common WHERE clause patterns.</p>',
            ],
            [
                'title' => 'Using Form Requests to Keep Controllers Clean',
                'description' => '<p>Form Requests move validation logic out of controllers and into dedicated classes, giving each request its own authorisation check and rule set.</p><p>This keeps controllers focused purely on orchestration rather than validation detail.</p>',
            ],
            [
                'title' => 'Soft Deletes and Audit Trails in Laravel',
                'description' => '<p>Pairing SoftDeletes with audit columns such as created_by, updated_by and deleted_by gives a full history of who changed a record and when, without losing the ability to restore it later.</p>',
            ],
            [
                'title' => 'Setting Up Spatie Permissions for Role-Based Access',
                'description' => '<p>Spatie\'s permission package makes it straightforward to define granular permissions and group them into roles, so authorisation checks stay declarative rather than scattered through if statements.</p>',
            ],
            [
                'title' => 'An Introduction to Inertia.js with Laravel and Vue',
                'description' => '<p>Inertia removes the need for a separate API layer by letting Laravel controllers return Vue pages directly, while still giving a single-page application feel in the browser.</p>',
            ],
            [
                'title' => 'Handling File Uploads Safely in Laravel',
                'description' => '<p>Validating MIME types and file size server-side is essential when accepting uploads, since client-side accept attributes can be bypassed entirely.</p><p>Storing files on a dedicated disk also keeps upload handling consistent across environments.</p>',
            ],
            [
                'title' => 'Writing Feature Tests with Pest',
                'description' => '<p>Pest\'s expressive syntax makes feature tests read closer to plain English, which helps keep test intent clear as a codebase grows.</p>',
            ],
            [
                'title' => 'Polymorphic Relationships for Shared Data Structures',
                'description' => '<p>A polymorphic relationship, such as an Address or Contact model, allows the same table structure to be reused across multiple parent models like Users and Companies without duplicating schema.</p>',
            ],
        ];

        foreach ($posts as $post) {
            Post::query()->create([
                ...$post,
                'created_by' => $author?->id,
            ]);
        }
    }
}
