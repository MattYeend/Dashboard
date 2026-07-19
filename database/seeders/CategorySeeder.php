<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Category::exists()) {
            $this->command->info('Categories already seeded, skipping...');

            return;
        }

        $users = User::all()->keyBy('email');

        if ($users->isEmpty()) {
            $this->command->warn('No users found, skipping address seeding...');

            return;
        }

        $categories = [
            [
                'name' => 'Web Development',
                'description' => 'General web development topics covering backend and frontend practices.',
                'children' => [
                    'Laravel',
                    'Vue.js',
                    'PHP',
                    'MySQL',
                ],
            ],
            [
                'name' => 'SEO',
                'description' => 'Search engine optimisation, PageSpeed performance, and Yoast SEO guidance.',
                'children' => [
                    'Technical SEO',
                ],
            ],
            [
                'name' => 'WordPress',
                'description' => 'WordPress performance, theming, and Cloudflare setup.',
            ],
            [
                'name' => 'Packagist Packages',
                'description' => 'Documentation and write-ups for open source Laravel packages.',
            ],
            [
                'name' => 'Career',
                'description' => 'Freelancing, career development, and working as a software developer.',
            ],
        ];

        foreach ($categories as $category) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                ]
            );

            foreach ($category['children'] ?? [] as $childName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'description' => null,
                    ]
                );
            }
        }
    }
}
