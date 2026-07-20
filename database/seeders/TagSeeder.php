<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Tag::exists()) {
            $this->command->info('Tags already seeded, skipping...');

            return;
        }

        $tags = [
            'Laravel',
            'PHP',
            'Vue.js',
            'MySQL',
            'Tutorial',
            'News',
            'Tips',
            'Release Notes',
        ];

        foreach ($tags as $name) {
            Tag::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }
}
