<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Comment::exists()) {
            $this->command->info('Comments already seeded, skipping...');

            return;
        }

        $users = User::all()->keyBy('email');

        if ($users->isEmpty()) {
            $this->command->warn('No users found, skipping address seeding...');

            return;
        }

        $posts = Post::all();
        $users = User::all();

        if ($posts->isEmpty() || $users->isEmpty()) {
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

            Comment::firstOrCreate(
                [
                    'post_id' => $post->id,
                    'created_by' => $author->id,
                    'content' => $comments[$index % count($comments)],
                ],
            );
        }
    }
}
