<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Comment::withTrashed()->withoutGlobalScope('organisation')->exists()) {
            $this->command->info('Comments already seeded, skipping...');

            return;
        }

        $organisation = $this->defaultOrganisation();

        $posts = Post::withoutGlobalScope('organisation')
            ->where('organisation_id', $organisation->id)
            ->get();
        $users = User::all();

        if ($posts->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No posts or users found, skipping comment seeding...');

            return;
        }

        $comments = [
            'Great write-up, this really helped me understand the topic.',
            'Thanks for sharing, looking forward to the next one.',
            'I disagree with a couple of points here, but overall a solid read.',
            'Could you expand on the second section a bit more?',
            'This is exactly what I was looking for, cheers.',
        ];

        foreach ($posts as $index => $post) {
            $author = $users[$index % $users->count()];

            Comment::factory()
                ->forModel($post)
                ->create([
                    'created_by' => $author->id,
                    'content' => $comments[$index % count($comments)],
                    'organisation_id' => $organisation->id,
                ]);
        }
    }
}
