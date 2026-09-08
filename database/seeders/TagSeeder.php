<?php

namespace Database\Seeders;

use App\Models\Tag;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Tag::withTrashed()->withoutGlobalScope('organisation')->exists()) {
            $this->command->info('Tags already seeded, skipping...');

            return;
        }

        $organisation = $this->defaultOrganisation();
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
                'organisation_id' => $organisation->id,
            ]);
        }
    }
}
