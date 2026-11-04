<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Organisation;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organisation = Organisation::query()->oldest('id')->firstOrFail();
        $organisation->makeCurrent();

        $actorId = User::query()->orderBy('id')->value('id');

        $data = [
            'Which file defines the web routes in a default Laravel application?' => [
                ['answer' => 'routes/web.php', 'is_correct' => true, 'feedback' => 'Web routes live in routes/web.php.'],
                ['answer' => 'routes/api.php', 'is_correct' => false, 'feedback' => 'This file is for stateless API routes.'],
                ['answer' => 'routes/console.php', 'is_correct' => false, 'feedback' => 'This file defines Artisan commands.'],
                ['answer' => 'app/Http/routes.php', 'is_correct' => false, 'feedback' => 'This file was removed in modern Laravel versions.'],
            ],
            'Route model binding can resolve an Eloquent model from a route parameter.' => [
                ['answer' => 'True', 'is_correct' => true, 'feedback' => 'Implicit binding matches the type-hinted model to the route segment.'],
                ['answer' => 'False', 'is_correct' => false, 'feedback' => null],
            ],
            'Which of these are valid HTTP verbs for Laravel route definitions?' => [
                ['answer' => 'GET', 'is_correct' => true, 'feedback' => null],
                ['answer' => 'POST', 'is_correct' => true, 'feedback' => null],
                ['answer' => 'PUT', 'is_correct' => true, 'feedback' => null],
                ['answer' => 'FETCH', 'is_correct' => false, 'feedback' => 'FETCH is not an HTTP verb.'],
            ],
            'Which macro lets a child component expose a two-way bound value?' => [
                ['answer' => 'defineModel', 'is_correct' => true, 'feedback' => 'defineModel creates a ref that syncs with the parent v-model.'],
                ['answer' => 'defineProps', 'is_correct' => false, 'feedback' => 'defineProps only declares one-way props.'],
                ['answer' => 'defineEmits', 'is_correct' => false, 'feedback' => 'defineEmits only declares emitted events.'],
                ['answer' => 'defineExpose', 'is_correct' => false, 'feedback' => 'defineExpose exposes methods to a parent ref.'],
            ],
            'A ref value is read in a script block with .value.' => [
                ['answer' => 'True', 'is_correct' => true, 'feedback' => 'Refs are unwrapped automatically in templates, but not in script.'],
                ['answer' => 'False', 'is_correct' => false, 'feedback' => null],
            ],
            'Which statement shows how MySQL plans to run a query?' => [
                ['answer' => 'EXPLAIN', 'is_correct' => true, 'feedback' => 'EXPLAIN shows the execution plan, including which indexes are used.'],
                ['answer' => 'DESCRIBE', 'is_correct' => false, 'feedback' => 'DESCRIBE shows a table structure.'],
                ['answer' => 'SHOW TABLES', 'is_correct' => false, 'feedback' => 'SHOW TABLES lists the tables in a database.'],
                ['answer' => 'ANALYZE TABLE', 'is_correct' => false, 'feedback' => 'ANALYZE TABLE updates index statistics.'],
            ],
            'Adding an index always makes writes faster.' => [
                ['answer' => 'True', 'is_correct' => false, 'feedback' => 'Every index has to be maintained on writes, so extra indexes slow them down.'],
                ['answer' => 'False', 'is_correct' => true, 'feedback' => 'Indexes speed up reads but add work to inserts, updates and deletes.'],
            ],
        ];

        foreach ($data as $questionText => $answers) {
            $question = Question::query()->where('question', $questionText)->first();

            if ($question === null) {
                $this->command?->warn("Question not found, skipping: {$questionText}");

                continue;
            }

            foreach ($answers as $index => $row) {
                $answer = Answer::query()->firstOrNew([
                    'question_id' => $question->id,
                    'answer' => $row['answer'],
                ]);

                $answer->forceFill([
                    'organisation_id' => $question->organisation_id,
                    'is_correct' => $row['is_correct'],
                    'feedback' => $row['feedback'],
                    'position' => $index + 1,
                    'created_by' => $answer->created_by ?? $actorId,
                ])->save();
            }
        }
    }
}
